<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\AdminTransaction;
use App\Models\AdminWallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DoctorWalletController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Balance = sum(credits) - sum(debits)
        $credits = WalletTransaction::forUser($user->id)->where('type', 'credit')->sum('amount');
        $debits  = WalletTransaction::forUser($user->id)->where('type', 'debit')->sum('amount');
        $balance = $user->wallet_balance;

        $transactions = WalletTransaction::with([])
            ->forUser($user->id)
            ->latestFirst()
            ->paginate(10)
            ->withQueryString();

        if ($request->ajax()) {
            return view('doctor.wallet._list', compact('transactions'))->render();
        }

        $fee = 23 / 100;

        return view('doctor.wallet.index', compact('balance', 'transactions', 'fee'));
    }

    public function addFunds(Request $request)
    {
        $data = $request->validate([
            'amount'   => 'required|numeric|min:5',
            'currency' => 'nullable|string|size:3'
        ]);

        $user = Auth::user();
        $currency = strtoupper($data['currency'] ?? 'USD');

        // TODO: integrate your payment gateway; for now we demo-credit immediately.
        $tx = WalletTransaction::create([
            'user_id'  => $user->id,
            'type'     => 'credit',
            'amount'   => $data['amount'],
            'currency' => $currency,
            'purpose'  => 'top_up',
            'reference' => 'TX-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(6)),
            'meta'     => ['source' => 'manual_demo', 'status' => 'success'],
        ]);

        $user->update(['wallet_balance' => $user->wallet_balance + $data['amount']]);

        return response()->json(['status' => 'success', 'message' => 'Funds added', 'tx' => $tx]);
    }

    public function withdraw(Request $request)
    {
        $data = $request->validate([
            'amount'   => 'required|numeric|min:5',
            'currency' => 'nullable|string|size:3'
        ]);

        $user = Auth::user();
        $currency = strtoupper($data['currency'] ?? 'USD');
        $fee = (23 / 100);
        $calculatedFee = $data['amount'] * $fee;
        $calculatedAmount = $data['amount'] - $calculatedFee;

        // Compute current balance to prevent overdraft
        $credits = WalletTransaction::forUser($user->id)->where('type', 'credit')->sum('amount');
        $debits  = WalletTransaction::forUser($user->id)->where('type', 'debit')->sum('amount');
        $balance = $user->wallet_balance;

        if ($data['amount'] > $balance) {
            return response()->json(['status' => 'error', 'message' => 'Insufficient balance'], 422);
        }

        // You might want a separate Withdrawals table; here we log as a debit with pending meta.
        $tx = WalletTransaction::create([
            'user_id'  => $user->id,
            'type'     => 'debit',
            'amount'   => $calculatedAmount,
            'fee'      => $calculatedFee,
            'currency' => $currency,
            'purpose'  => 'withdrawal_request',
            'reference' => 'TX-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(6)),
            'meta'     => ['status' => 'pending'],
        ]);

        //add fee to admin wallet
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
            'purpose'  => 'withdrawal_request_fee_from_doctor',
            'reference' => 'TX-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(6)),
            'meta'     => ['status' => 'success'],
        ]);



        $user->update(['wallet_balance' => $user->wallet_balance - $data['amount']]);

        return response()->json(['status' => 'success', 'message' => 'Withdrawal requested', 'tx' => $tx]);
    }
}
