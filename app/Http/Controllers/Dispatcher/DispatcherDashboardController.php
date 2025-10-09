<?php

namespace App\Http\Controllers\Dispatcher;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class DispatcherDashboardController extends Controller
{
    public function index(Request $request)
    {
        $dispatcherId = Auth::id();

        // PENDING (unassigned): orders pharmacy marked ready, or in fee stages but not yet claimed
        $pendingOrders = Order::with([
            'prescription.patient:id,first_name,last_name,phone,address',
            'prescription.pharmacy:id,first_name,last_name,address',
            'prescription:id,code,patient_id,pharmacy_id',
        ])
            ->whereNull('dispatcher_id')
            ->whereIn('status', [
                'ready',
                'dispatcher_price_set',
                'dispatcher_price_confirm',
            ])
            ->latest()
            ->paginate(10, ['*'], 'pending_page');

        // ACTIVE (mine): claimed orders in active delivery statuses
        $activeOrders = Order::with([
            'prescription.patient:id,first_name,last_name,phone,address',
            'prescription.pharmacy:id,first_name,last_name,address',
            'prescription:id,code,patient_id,pharmacy_id',
        ])
            ->where('dispatcher_id', $dispatcherId)
            ->whereIn('status', [
                'ready',
                'dispatcher_price_set',
                'dispatcher_price_confirm',
                'picked',
            ])
            ->latest()
            ->paginate(10, ['*'], 'active_page');

        $pendingCount = $pendingOrders->total();
        $activeCount  = $activeOrders->total();

        // Revenue today = sum of dispatcher fees on orders you delivered today
        $today = Carbon::today();
        $revenueToday = Order::where('dispatcher_id', $dispatcherId)
            ->where('status', 'delivered')
            ->whereDate('updated_at', $today)
            ->sum('dispatcher_price');

        return view('dispatcher.dashboard', compact(
            'pendingOrders',
            'activeOrders',
            'pendingCount',
            'activeCount',
            'revenueToday'
        ));
    }

    /* Wallet pages you already had — unchanged except they live here for convenience */

    public function walletIndex(Request $request)
    {
        $user = $request->user();
        $transactions = WalletTransaction::forUser($user->id)->latestFirst()->paginate(10)->withQueryString();
        $balance = $user->wallet_balance;
        $fee = (23 / 100);

        if ($request->ajax()) {
            return view('dispatcher.wallet._list', compact('transactions'))->render();
        }

        return view('dispatcher.wallet.index', compact('balance', 'transactions', 'fee'));
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

        $user->fill($data)->save();

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

    // Deliveries list/history using Orders
    public function getDeliveries(Request $request)
    {
        $dispatcherId = Auth::id();

        $q      = trim((string) $request->get('q'));
        $status = (string) $request->get('status'); // '', picked, delivered, cancelled, etc
        $from   = $request->date('from');
        $to     = $request->date('to');

        $base = Order::with(['prescription.patient', 'prescription.pharmacy'])
            ->where('dispatcher_id', $dispatcherId);

        if ($status !== '') {
            $base->where('status', $status);
        } else {
            $base->whereIn('status', ['picked', 'delivered', 'cancelled']);
        }

        if ($q !== '') {
            $base->where(function ($w) use ($q) {
                $w->whereHas('prescription', fn($pq) => $pq->where('code', 'like', "%{$q}%"))
                    ->orWhereHas('prescription.patient', fn($p) => $p
                        ->where('first_name', 'like', "%{$q}%")
                        ->orWhere('last_name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%"))
                    ->orWhereHas('prescription.pharmacy', fn($ph) => $ph
                        ->where('first_name', 'like', "%{$q}%")
                        ->orWhere('last_name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%"));
            });
        }

        if ($from) $base->whereDate('updated_at', '>=', $from);
        if ($to)   $base->whereDate('updated_at', '<=', $to);

        $deliveries = $base->orderByDesc('updated_at')->paginate(15)->withQueryString();

        $countPicked    = (clone $base)->where('status', 'picked')->count();
        $countDelivered = (clone $base)->where('status', 'delivered')->count();
        $countCancelled = (clone $base)->where('status', 'cancelled')->count();
        $sumFees        = (clone $base)->sum('dispatcher_price');

        return view('dispatcher.deliveries.index', compact(
            'deliveries',
            'q',
            'status',
            'from',
            'to',
            'countPicked',
            'countDelivered',
            'countCancelled',
            'sumFees'
        ));
    }
}
