<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AdminWithdrawalRequestController extends Controller
{
    // app/Http/Controllers/Admin/WithdrawalController.php
    public function index(Request $request)
    {
        // read from either ?status=&search= (view) or ?q=
        $status = (string) $request->query('status', '');
        $search = trim((string) ($request->query('search') ?? $request->query('q') ?? ''));

        $allowedStatuses = ['pending', 'approved', 'paid', 'rejected', 'failed'];

        $withdrawals = \App\Models\WithdrawalRequest::with('user')
            // only apply status filter when it's a known status
            ->when(in_array($status, $allowedStatuses, true), function ($q) use ($status) {
                $q->where('status', $status);
            })
            // search by user (name/email) or reference
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('reference', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($u) use ($search) {
                            $u->where('email', 'like', "%{$search}%")
                                ->orWhere('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(20)
            ->appends($request->query()); // keep filters in pagination links

        // pass $search if you want, but your blade already uses request('search')
        return view('admin.withdrawals.index', compact('withdrawals', 'status'));
    }


    public function approve(Request $r, WithdrawalRequest $wd)
    {
        if (!in_array($wd->status, ['pending', 'approved'], true)) {
            return back()->with('error', 'Invalid state for approval.');
        }

        // Approve first
        $wd->status      = 'approved';
        $wd->approved_at = now();
        $wd->approved_by = $r->user()->id;
        $wd->save();

        // Auto-payout right away
        try {
            $payload = [
                'account_bank'   => $wd->bank_code,
                'account_number' => $wd->account_number,
                'amount'         => (float)$wd->final_amount,
                'currency'       => $wd->currency,
                'narration'      => config('app.name') . ' withdrawal ' . $wd->reference,
                'reference'      => $wd->reference, // idempotency
                'debit_currency' => $wd->currency,
                'meta'           => array_filter([
                    'account_name'   => $wd->account_name,
                    'bank_name'      => $wd->bank_name,
                ]),
            ];

            $res = Http::withToken(config('services.flutterwave.secret'))
                ->post(rtrim(config('services.flutterwave.base_url'), '/') . '/transfers', $payload)
                ->json();

            if (($res['status'] ?? '') !== 'success') {
                Log::warning('Flutterwave transfer error (auto payout)', ['ref' => $wd->reference, 'res' => $res]);

                // Refund + mark failed atomically
                DB::transaction(function () use ($wd, $res) {
                    // refund wallet
                    $user = $wd->user()->lockForUpdate()->first();
                    $user->wallet_balance = (float)$user->wallet_balance + (float)$wd->amount;
                    $user->save();

                    // mark hold tx as failed
                    WalletTransaction::where('reference', $wd->reference)
                        ->update(['status' => 'failed', 'purpose' => 'Withdrawal (payout failed)']);

                    $wd->status = 'failed';
                    $wd->save();
                });

                return back()->with('error', $res['message'] ?? 'Payout failed (auto). Funds refunded.');
            }

            // Success: mark paid + finalize ledger
            DB::transaction(function () use ($wd, $res) {
                $wd->status  = 'paid';
                $wd->paid_at = now();
                $meta = $wd->meta ?? [];
                $meta['flutterwave'] = ['response' => $res['data'] ?? null];
                $wd->meta = $meta;
                $wd->save();

                WalletTransaction::where('reference', $wd->reference)
                    ->update(['status' => 'successful', 'purpose' => 'Withdrawal']);
            });

            return back()->with('success', 'Withdrawal approved & paid out.');
        } catch (\Throwable $e) {
            Log::error('Auto payout exception', ['ref' => $wd->reference, 'err' => $e->getMessage()]);

            // Refund + mark failed on unexpected exception
            DB::transaction(function () use ($wd, $e) {
                $user = $wd->user()->lockForUpdate()->first();
                $user->wallet_balance = (float)$user->wallet_balance + (float)$wd->amount;
                $user->save();

                WalletTransaction::where('reference', $wd->reference)
                    ->update(['status' => 'failed', 'purpose' => 'Withdrawal (exception refund)']);

                $wd->status = 'failed';
                $wd->save();
            });

            return back()->with('error', 'Unexpected error during payout. Funds refunded.');
        }
    }

    public function reject(Request $r, WithdrawalRequest $wd)
    {
        if (!in_array($wd->status, ['pending', 'approved'], true)) {
            return back()->with('error', 'Invalid state for rejection.');
        }

        try {
            DB::transaction(function () use ($wd, $r) {
                // Refund user wallet (reverse the hold)
                $user = $wd->user()->lockForUpdate()->first();
                $user->wallet_balance = (float)$user->wallet_balance + (float)$wd->amount;
                $user->save();

                // Mark the hold transaction as failed
                WalletTransaction::where('reference', $wd->reference)
                    ->update(['status' => 'failed', 'purpose' => 'Withdrawal (rejected)']);

                $wd->status      = 'rejected';
                $wd->rejected_at = now();
                $wd->rejected_by = $r->user()->id;
                $wd->save();
            });
            return back()->with('success', 'Withdrawal rejected and refunded.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to reject: ' . $e->getMessage());
        }
    }
}
