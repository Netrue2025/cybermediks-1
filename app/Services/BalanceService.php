<?php

namespace App\Services;

use App\Models\User;
use App\Models\Appointment;
use App\Models\WalletHold;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BalanceService
{
    /** Create a HOLD (no balance change). */
    public static function holdAppointment(Appointment $ap, float $amount): WalletHold
    {
        return DB::transaction(function () use ($ap, $amount) {
            $hold = WalletHold::create([
                'source_user_id' => $ap->patient_id, // patient pays
                'target_user_id' => $ap->doctor_id,  // doctor receives
                'amount'        => $amount,
                'status'        => 'pending',
                'ref_type'      => 'appointment',
                'ref_id'        => $ap->id,
            ]);

            // Audit row (patient)
            WalletTransaction::create([
                'user_id'   => $ap->patient_id,
                'type'      => 'hold',                       // no balance change
                'amount'    => $amount,
                'currency'  => 'NGN',
                'purpose'   => 'appointment_hold',
                'reference' => 'appointment:'.$ap->id,
                'meta'      => ['hold_id' => $hold->id, 'status' => 'success'],
            ]);

            return $hold;
        });
    }

    /** Capture full hold: move from patient → doctor. */
    public static function captureHoldForAppointment(Appointment $ap): void
    {
        DB::transaction(function () use ($ap) {
            $hold = WalletHold::where([
                'ref_type' => 'appointment',
                'ref_id'   => $ap->id,
                'status'   => 'pending',
            ])->lockForUpdate()->firstOrFail();

            $patient = User::whereKey($hold->source_user_id)->lockForUpdate()->first();
            $doctor  = User::whereKey($hold->target_user_id)->lockForUpdate()->first();

            if ((float)$patient->wallet_balance < (float)$hold->amount) {
                throw new InvalidArgumentException('Insufficient patient balance to capture.');
            }

            // Move balances
            $patient->wallet_balance = (float)$patient->wallet_balance - (float)$hold->amount;
            $doctor->wallet_balance  = (float)$doctor->wallet_balance  + (float)$hold->amount;
            $patient->save();
            $doctor->save();

            $hold->update(['status' => 'captured']);

            // Transactions
            WalletTransaction::create([
                'user_id'   => $patient->id,
                'type'      => 'debit',
                'amount'    => $hold->amount,
                'currency'  => 'NGN',
                'purpose'   => 'appointment_capture',
                'reference' => 'appointment:'.$ap->id,
                'meta'      => ['hold_id' => $hold->id],
                'status'    => 'ok',
            ]);
            WalletTransaction::create([
                'user_id'   => $doctor->id,
                'type'      => 'credit',
                'amount'    => $hold->amount,
                'currency'  => 'NGN',
                'purpose'   => 'appointment_capture',
                'reference' => 'appointment:'.$ap->id,
                'meta'      => ['hold_id' => $hold->id],
                'status'    => 'ok',
            ]);
        });
    }

    /** Release hold back to patient (audit only). */
    public static function releaseHoldToPatient(Appointment $ap): void
    {
        DB::transaction(function () use ($ap) {
            $hold = WalletHold::where([
                'ref_type' => 'appointment',
                'ref_id'   => $ap->id,
                'status'   => 'pending',
            ])->lockForUpdate()->firstOrFail();

            $hold->update(['status' => 'released']);

            WalletTransaction::create([
                'user_id'   => $hold->source_user_id,
                'type'      => 'release',                // no balance change
                'amount'    => $hold->amount,
                'currency'  => 'NGN',
                'purpose'   => 'appointment_release',
                'reference' => 'appointment:'.$ap->id,
                'meta'      => ['hold_id' => $hold->id],
                'status'    => 'ok',
            ]);
        });
    }

    /** Partial: capture part to doctor, release remainder to patient (audit). */
    public static function partialCaptureAndRelease(Appointment $ap, float $captureAmount): void
    {
        DB::transaction(function () use ($ap, $captureAmount) {
            $hold = WalletHold::where([
                'ref_type' => 'appointment',
                'ref_id'   => $ap->id,
                'status'   => 'pending',
            ])->lockForUpdate()->firstOrFail();

            if ($captureAmount < 0 || $captureAmount > (float)$hold->amount) {
                throw new InvalidArgumentException('Invalid partial capture amount.');
            }

            $patient = User::whereKey($hold->source_user_id)->lockForUpdate()->first();
            $doctor  = User::whereKey($hold->target_user_id)->lockForUpdate()->first();

            if ($captureAmount > 0) {
                if ((float)$patient->wallet_balance < $captureAmount) {
                    throw new InvalidArgumentException('Insufficient patient balance for partial capture.');
                }
                $patient->wallet_balance = (float)$patient->wallet_balance - $captureAmount;
                $doctor->wallet_balance  = (float)$doctor->wallet_balance  + $captureAmount;
                $patient->save();
                $doctor->save();

                WalletTransaction::create([
                    'user_id'   => $patient->id,
                    'type'      => 'debit',
                    'amount'    => $captureAmount,
                    'currency'  => 'NGN',
                    'purpose'   => 'appointment_capture',
                    'reference' => 'appointment:'.$ap->id,
                    'meta'      => ['hold_id' => $hold->id, 'partial' => true],
                    'status'    => 'ok',
                ]);
                WalletTransaction::create([
                    'user_id'   => $doctor->id,
                    'type'      => 'credit',
                    'amount'    => $captureAmount,
                    'currency'  => 'NGN',
                    'purpose'   => 'appointment_capture',
                    'reference' => 'appointment:'.$ap->id,
                    'meta'      => ['hold_id' => $hold->id, 'partial' => true],
                    'status'    => 'ok',
                ]);
            }

            $releaseAmount = (float)$hold->amount - $captureAmount;
            if ($releaseAmount > 0) {
                WalletTransaction::create([
                    'user_id'   => $patient->id,
                    'type'      => 'release',
                    'amount'    => $releaseAmount,
                    'currency'  => 'NGN',
                    'purpose'   => 'appointment_release',
                    'reference' => 'appointment:'.$ap->id,
                    'meta'      => ['hold_id' => $hold->id, 'partial' => true],
                    'status'    => 'ok',
                ]);
            }

            $hold->update(['status' => 'partial']);
        });
    }

    /** If money was already captured earlier, refund now (doctor → patient). */
    public static function refundCaptured(Appointment $ap, float $amount): void
    {
        DB::transaction(function () use ($ap, $amount) {
            $hold    = WalletHold::where(['ref_type'=>'appointment','ref_id'=>$ap->id])->firstOrFail();
            $patient = User::whereKey($hold->source_user_id)->lockForUpdate()->first();
            $doctor  = User::whereKey($hold->target_user_id)->lockForUpdate()->first();

            if ((float)$doctor->wallet_balance < $amount) {
                throw new InvalidArgumentException('Doctor has insufficient balance to refund.');
            }

            $doctor->wallet_balance  = (float)$doctor->wallet_balance  - $amount;
            $patient->wallet_balance = (float)$patient->wallet_balance + $amount;
            $doctor->save(); $patient->save();

            WalletTransaction::create([
                'user_id'   => $doctor->id,
                'type'      => 'debit',
                'amount'    => $amount,
                'currency'  => 'NGN',
                'purpose'   => 'appointment_refund',
                'reference' => 'appointment:'.$ap->id,
                'meta'      => ['refund' => true],
                'status'    => 'ok',
            ]);
            WalletTransaction::create([
                'user_id'   => $patient->id,
                'type'      => 'credit',
                'amount'    => $amount,
                'currency'  => 'NGN',
                'purpose'   => 'appointment_refund',
                'reference' => 'appointment:'.$ap->id,
                'meta'      => ['refund' => true],
                'status'    => 'ok',
            ]);
        });
    }
}
