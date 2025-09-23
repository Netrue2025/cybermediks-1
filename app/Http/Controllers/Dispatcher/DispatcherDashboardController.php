<?php

namespace App\Http\Controllers\Dispatcher;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class DispatcherDashboardController extends Controller
{
    public function index(Request $request)
    {
        $dispatcherId = Auth::id();

        // READY & no dispatcher yet => pending claims
        $pending = Prescription::with(['patient', 'pharmacy', 'items'])
            ->where('dispense_status', 'ready')
            ->whereNull('dispatcher_id')
            ->latest()
            ->paginate(10, ['*'], 'pending_page');

        // READY & assigned to me => active deliveries
        $active = Prescription::with(['patient', 'pharmacy', 'items'])
            ->where('dispense_status', 'ready')
            ->where('dispatcher_id', $dispatcherId)
            ->latest()
            ->paginate(10, ['*'], 'active_page');

        $pendingCount = $pending->total();
        $activeCount  = $active->total();

        // If you want, compute real revenue from deliveries today; for now, your wallet:
        $revenueToday = Auth::user()->wallet_balance ?? 0;

        return view('dispatcher.dashboard', compact(
            'pending',
            'active',
            'pendingCount',
            'activeCount',
            'revenueToday'
        ));
    }

    public function walletIndex(Request $request)
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
            return view('dispatcher.wallet._list', compact('transactions'))->render();
        }

        return view('dispatcher.wallet.index', compact('balance', 'transactions'));
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
            'amount'   => $data['amount'],
            'currency' => $currency,
            'purpose'  => 'withdrawal_request',
            'reference' => 'TX-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(6)),
            'meta'     => ['status' => 'pending'],
        ]);

        $user->update(['wallet_balance' => $user->wallet_balance - $data['amount']]);

        return response()->json(['status' => 'success', 'message' => 'Withdrawal requested', 'tx' => $tx]);
    }

    public function showProfile()
    {
        return view('dispatcher.profile');
    }

    public function updateProfile(Request $r)
    {
        $user = $r->user();

        $data = $r->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['nullable', 'string', 'max:100'],
            'phone'      => ['nullable', 'string', 'max:40'],
            'gender'     => ['nullable', 'in:male,female,other'],
            'dob'        => ['nullable', 'date'],
            'country'    => ['nullable', 'string', 'max:100'],
            'address'    => ['nullable', 'string', 'max:255'],
        ]);

        $user->fill([
            'first_name'    => $data['first_name'],
            'last_name'     => $data['last_name'],
            'phone'   => $data['phone'] ?? null,
            'gender'  => $data['gender'] ?? null,
            'dob'     => $data['dob'] ?? null,
            'country' => $data['country'] ?? null,
            'address' => $data['address'] ?? null,
        ])->save();

        return response()->json(['ok' => true, 'message' => 'Profile updated']);
    }

    public function updatePassword(Request $r)
    {
        $r->validate([
            'current_password' => ['required'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        $user = $r->user();

        if (!Hash::check($r->current_password, $user->password)) {
            return response()->json(['ok' => false, 'message' => 'Current password is incorrect'], 422);
        }

        $user->update(['password' => Hash::make($r->password)]);
        return response()->json(['ok' => true, 'message' => 'Password updated']);
    }
}
