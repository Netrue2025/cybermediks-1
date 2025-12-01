<?php

namespace App\Http\Controllers;

use App\Models\AdminTransaction;
use App\Models\AdminWallet;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WithdrawalRequestController extends Controller
{
    public function requestWithdraw(Request $r)
    {
        $data = $r->validate([
            'amount'        => 'required|numeric|min:5',
            'currency'      => 'nullable|string|in:NGN',
            // payout details
            'bank_name'     => 'required|string|max:120',
            'bank_code'     => 'required|string|max:40',     // Flutterwave bank code
            'account_number' => 'required|string|max:40',
            'account_name'  => 'required|string|max:120',
        ]);

        $user     = $r->user();
        $amount   = (float)$data['amount'];
        $currency = $data['currency'] ?? 'NGN';
        if ($user->role === 'pharmacy') {
            $fee = (8 / 100);
        } else {
            $fee = (23 / 100);
        }

        $calculatedFee = $amount * $fee;
        $calculatedAmount = $amount - $calculatedFee;

        if ($user->role === 'patient' || $user->role === 'doctor' || $user->role === 'admin') {
            return response()->json(['message' => 'You are not allowed to make a withdrawal'], 422);
        }

        if (($user->wallet_balance ?? 0) < $amount) {
            return response()->json(['message' => 'Insufficient balance'], 422);
        }

        try {
            DB::transaction(function () use ($user, $amount, $currency, $data, $calculatedAmount, $calculatedFee) {
                // Hold funds immediately by decreasing wallet_balance + log tx
                $user->wallet_balance = (float)$user->wallet_balance - $amount;
                $user->save();

                WalletTransaction::create([
                    'user_id'   => $user->id,
                    'type'      => 'debit',
                    'amount'    => $calculatedAmount,
                    'fee'       => $calculatedFee,
                    'reference' => $ref = 'WD-' . Str::uuid()->toString(),
                    'purpose'   => 'Withdrawal (hold)',
                    'meta'      => json_encode(['channel' => 'withdrawal', 'currency' => $currency]),
                    'status'    => 'pending',
                ]);

                WithdrawalRequest::create([
                    'user_id'        => $user->id,
                    'amount'         => $amount,
                    'currency'       => $currency,
                    'status'         => 'pending',
                    'reference'      => $ref,
                    'payout_channel' => 'flutterwave',
                    'bank_name'      => $data['bank_name'],
                    'bank_code'      => $data['bank_code'],
                    'account_number' => $data['account_number'],
                    'account_name'   => $data['account_name'],
                    'meta'           => ['note' => 'withdrawal requested'],
                ]);

                $wallet = AdminWallet::first();

                if ($wallet) {
                    $wallet->update(['balance' => $wallet->balance + $calculatedFee]);
                } else {
                    AdminWallet::create([
                        'balance' => $calculatedFee
                    ]);
                }

                AdminTransaction::create([
                    'type'     => 'debit',
                    'amount'   => $calculatedFee,
                    'currency' => $currency,
                    'purpose'  => 'withdrawal_request_fee_from_' . $user->role,
                    'reference' => 'TX-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(6)),
                    'meta'     => ['status' => 'success'],
                ]);
            });

            return response()->json(['message' => 'Withdrawal requested. You’ll be notified when processed.']);
        } catch (\Throwable $e) {
            Log::error('Withdrawal request failed', ['err' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to request withdrawal'], 500);
        }
    }
}
