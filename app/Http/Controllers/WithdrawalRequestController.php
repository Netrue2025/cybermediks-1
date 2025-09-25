<?php

namespace App\Http\Controllers;

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
            'currency'      => 'nullable|string|in:USD',
            // payout details (required for USD transfers)
            'bank_name'     => 'required|string|max:120',
            'bank_code'     => 'required|string|max:40',     // Flutterwave bank code for USD route
            'account_number'=> 'required|string|max:40',
            'account_name'  => 'required|string|max:120',
            'routing_number'=> 'nullable|string|max:40',
            'swift_code'    => 'nullable|string|max:40',
        ]);

        $user     = $r->user();
        $amount   = (float)$data['amount'];
        $currency = $data['currency'] ?? 'USD';

        if ($user->role === 'patient' || $user->role === 'admin')
        {
            return response()->json(['message'=>'You are not allowed to make a withdrawal'], 422);
        }

        if (($user->wallet_balance ?? 0) < $amount) {
            return response()->json(['message'=>'Insufficient balance'], 422);
        }

        try {
            DB::transaction(function () use ($user, $amount, $currency, $data) {
                // Hold funds immediately by decreasing wallet_balance + log tx
                $user->wallet_balance = (float)$user->wallet_balance - $amount;
                $user->save();

                WalletTransaction::create([
                    'user_id'   => $user->id,
                    'type'      => 'debit',
                    'amount'    => $amount,
                    'reference' => $ref = 'WD-'.Str::uuid()->toString(),
                    'purpose'   => 'Withdrawal (hold)',
                    'meta'      => json_encode(['channel'=>'withdrawal','currency'=>$currency]),
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
                    'routing_number' => $data['routing_number'] ?? null,
                    'swift_code'     => $data['swift_code'] ?? null,
                    'meta'           => ['note'=>'doctor requested withdrawal'],
                ]);
            });

            return response()->json(['message'=>'Withdrawal requested. You’ll be notified when processed.']);
        } catch (\Throwable $e) {
            Log::error('Withdrawal request failed', ['err'=>$e->getMessage()]);
            return response()->json(['message'=>'Failed to request withdrawal'], 500);
        }
    }
}
