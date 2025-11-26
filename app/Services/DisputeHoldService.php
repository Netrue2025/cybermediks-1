<?php

namespace App\Services;

use App\Models\User;
use App\Models\Appointment;
use App\Models\WalletHold;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DisputeHoldService
{
    /** Called when the PATIENT opens a dispute.
     *  Deduct full amount from DOCTOR immediately and park on hold.
     */
    public static function openDoctorHold(Appointment $ap, float $amount, int $patientId, int $doctorId, ?int $disputeId = null): WalletHold
    {
        return DB::transaction(function () use ($ap, $amount, $patientId, $doctorId, $disputeId) {
            $doctor  = User::whereKey($doctorId)->lockForUpdate()->firstOrFail();

            if ((float)$doctor->wallet_balance < $amount) {
                throw new InvalidArgumentException('Doctor has insufficient balance to place dispute hold.');
            }

            // Deduct from doctor's wallet immediately
            $doctor->wallet_balance = (float)$doctor->wallet_balance - $amount;
            $doctor->save();

            // Create hold
            $hold = WalletHold::create([
                'source_user_id' => $doctor->id,
                'target_user_id' => $patientId,
                'amount'         => $amount,
                'status'         => 'pending',
                'ref_type'       => 'appointment',
                'ref_id'         => $ap->id,
                'meta'           => ['dispute_id' => $disputeId],
            ]);

            // Ledger (your existing wallet_transactions)
            WalletTransaction::create([
                'user_id'   => $doctor->id,
                'type'      => 'debit', // balance changed now
                'amount'    => $amount,
                'currency'  => 'NGN',
                'purpose'   => 'dispute_hold',            // <— clear purpose
                'reference' => 'appointment:' . $ap->id,
                'meta'      => ['hold_id' => $hold->id, 'dispute_id' => $disputeId],
                'status'    => 'ok',
            ]);

            return $hold;
        });
    }

    /** Admin action: REFUND — send ALL to PATIENT. */
    public static function refundToPatient(Appointment $ap): void
    {
        DB::transaction(function () use ($ap) {
            $hold = WalletHold::where([
                'ref_type' => 'appointment',
                'ref_id'   => $ap->id,
                'status'   => 'pending',
            ])->lockForUpdate()->firstOrFail();

            $patient = User::whereKey($hold->target_user_id)->lockForUpdate()->firstOrFail();

            // Credit patient full amount
            $patient->wallet_balance = (float)$patient->wallet_balance + (float)$hold->amount;
            $patient->save();

            $hold->update(['status' => 'released_to_patient']);

            WalletTransaction::create([
                'user_id'   => $patient->id,
                'type'      => 'credit',
                'amount'    => $hold->amount,
                'currency'  => 'NGN',
                'purpose'   => 'dispute_refund',
                'reference' => 'appointment:' . $ap->id,
                'meta'      => ['hold_id' => $hold->id],
                'status'    => 'ok',
            ]);

            // Optional: mark the *original* doctor capture tx as disputed in your UI by linking via meta
            // Or add a zero-amount "dispute_closed" entry if you prefer.
        });
    }

    /** Admin action: RELEASE — pay ALL back to DOCTOR. */
    public static function releaseBackToDoctor(Appointment $ap): void
    {
        DB::transaction(function () use ($ap) {
            $hold   = WalletHold::where([
                'ref_type' => 'appointment',
                'ref_id'   => $ap->id,
                'status'   => 'pending',
            ])->lockForUpdate()->firstOrFail();

            $doctor = User::whereKey($hold->source_user_id)->lockForUpdate()->firstOrFail();

            // Give doctor back the held amount
            $doctor->wallet_balance = (float)$doctor->wallet_balance + (float)$hold->amount;
            $doctor->save();

            $hold->update(['status' => 'released_to_doctor']);

            WalletTransaction::create([
                'user_id'   => $doctor->id,
                'type'      => 'credit',
                'amount'    => $hold->amount,
                'currency'  => 'NGN',
                'purpose'   => 'dispute_release',
                'reference' => 'appointment:' . $ap->id,
                'meta'      => ['hold_id' => $hold->id],
                'status'    => 'ok',
            ]);
        });
    }

    /** Admin action: PARTIAL — split 50/50 (handle cent rounding). */
    public static function partialSplit(Appointment $ap): void
    {
        DB::transaction(function () use ($ap) {
            $hold   = WalletHold::where([
                'ref_type' => 'appointment',
                'ref_id'   => $ap->id,
                'status'   => 'pending',
            ])->lockForUpdate()->firstOrFail();

            $doctor  = User::whereKey($hold->source_user_id)->lockForUpdate()->firstOrFail();
            $patient = User::whereKey($hold->target_user_id)->lockForUpdate()->firstOrFail();

            // Split with cent safety (e.g., $10.01 -> $5.01/$5.00)
            $amount = (float)$hold->amount;
            $half   = floor($amount * 100 / 2) / 100.0;     // lower half
            $other  = round($amount - $half, 2);            // remainder

            // Credit both sides
            $patient->wallet_balance = (float)$patient->wallet_balance + $half;
            $doctor->wallet_balance  = (float)$doctor->wallet_balance  + $other;
            $patient->save();
            $doctor->save();

            $hold->update([
                'status' => 'partial',
                'meta'   => array_merge($hold->meta ?? [], [
                    'split_patient' => $half,
                    'split_doctor'  => $other,
                ]),
            ]);

            WalletTransaction::create([
                'user_id'   => $patient->id,
                'type'      => 'credit',
                'amount'    => $half,
                'currency'  => 'NGN',
                'purpose'   => 'dispute_partial_patient',
                'reference' => 'appointment:' . $ap->id,
                'meta'      => ['hold_id' => $hold->id],
                'status'    => 'ok',
            ]);

            WalletTransaction::create([
                'user_id'   => $doctor->id,
                'type'      => 'credit',
                'amount'    => $other,
                'currency'  => 'NGN',
                'purpose'   => 'dispute_partial_doctor',
                'reference' => 'appointment:' . $ap->id,
                'meta'      => ['hold_id' => $hold->id],
                'status'    => 'ok',
            ]);
        });
    }
}
