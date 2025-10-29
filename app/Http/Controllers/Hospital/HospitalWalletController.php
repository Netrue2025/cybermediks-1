<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Models\AdminTransaction;
use App\Models\AdminWallet;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HospitalWalletController extends Controller
{

    public function index(Request $request)
    {
        $user = Auth::user(); // hospital account

        // Only sweep on the main page load (not on AJAX partial refreshes)
        if (! $request->ajax()) {
            DB::transaction(function () use ($user) {
                // Lock all doctors with positive balances under this hospital
                $doctors = User::where('hospital_id', $user->id)
                    ->where('wallet_balance', '>', 0)
                    ->lockForUpdate()
                    ->get(['id', 'wallet_balance']);

                $sum = $doctors->sum('wallet_balance');

                if ($sum > 0) {
                    // Zero-out doctors' balances
                    $ids = $doctors->pluck('id');
                    User::whereIn('id', $ids)->update(['wallet_balance' => 0]);

                    // Atomically credit the hospital's wallet
                    User::whereKey($user->id)->update([
                        'wallet_balance' => DB::raw('wallet_balance + ' . $sum),
                    ]);

                    // Optional: also update the in-memory $user so later reads reflect the new balance
                    $user->refresh();
                }
            });
        }

        // Recompute balances after sweep (reflects updated values)
        $patientsBalance = User::where('hospital_id', $user->id)->sum('wallet_balance'); // likely 0 after sweep
        $totalBalanceWithHospital = $patientsBalance + ($user->wallet_balance ?? 0);

        // Transactions list (unchanged), limited to this hospital's users
        $transactions = WalletTransaction::with(['user:id,first_name,last_name,email'])
            ->where(function ($q) use ($user) {
                // (1) Hospital's own transactions
                $q->where('user_id', $user->id)
                    // (2) Any user's transactions whose hospital_id = current hospital
                    ->orWhereHas('user', fn($u) => $u->where('hospital_id', $user->id));
            })
            ->latestFirst()
            ->paginate(10)
            ->withQueryString();


        if ($request->ajax()) {
            return view('hospital.wallet._list', compact('transactions'))->render();
        }

        $fee = 23 / 100;

        return view('hospital.wallet.index', [
            'transactions' => $transactions,
            'fee'          => $fee,
            'balance'      => $totalBalanceWithHospital,
        ]);
    }
}
