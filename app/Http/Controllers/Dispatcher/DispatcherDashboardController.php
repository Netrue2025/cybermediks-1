<?php

namespace App\Http\Controllers\Dispatcher;

use App\Http\Controllers\Controller;
use App\Models\Country;
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

        // dispatcher coordinates (adjust if stored elsewhere)
        $dLat = (float) (auth()->user()->lat ?? 0);
        $dLng = (float) (auth()->user()->lng ?? 0);
        $radiusKm = (float) ($request->query('radius_km', 20));

        // Haversine using pharmacy user (pharm.lat/lng). If on profile, see note below.
        $haversine = "(
            6371 * 2 * ASIN(SQRT(
                POWER(SIN(RADIANS(? - pharm.lat) / 2), 2) +
                COS(RADIANS(?)) * COS(RADIANS(pharm.lat)) *
                POWER(SIN(RADIANS(? - pharm.lng) / 2), 2)
            ))
        )";

        // ---------- PENDING (unassigned, near me) ----------
        $pendingQuery = Order::query()
            ->with([
                'prescription.patient:id,first_name,last_name,phone,address',
                'prescription.pharmacy:id,first_name,last_name,address,lat,lng',
                'prescription:id,code,patient_id,pharmacy_id',
            ])
            ->join('prescriptions as pr', 'pr.id', '=', 'orders.prescription_id')
            ->join('users as pharm', 'pharm.id', '=', 'pr.pharmacy_id')
            ->select('orders.*') // important: avoid ambiguous select columns
            ->whereNull('orders.dispatcher_id')
            ->whereIn('orders.status', ['ready', 'dispatcher_price_set', 'dispatcher_price_confirm']);

        if ($dLat && $dLng) {
            $pendingQuery
                ->selectRaw("$haversine as distance_km", [$dLat, $dLat, $dLng])
                ->whereNotNull('pharm.lat')
                ->whereNotNull('pharm.lng')
                ->having('distance_km', '<=', $radiusKm)
                ->orderBy('distance_km');
        } else {
            $pendingQuery->latest('orders.id');
        }

        $pendingOrders = $pendingQuery->paginate(10, ['*'], 'pending_page');

        // ---------- ACTIVE (mine) ----------
        $activeOrders = Order::with([
            'prescription.patient:id,first_name,last_name,phone,address',
            'prescription.pharmacy:id,first_name,last_name,address,lat,lng',
            'prescription:id,code,patient_id,pharmacy_id',
        ])
            ->where('orders.dispatcher_id', $dispatcherId)   // qualified
            ->whereIn('orders.status', [                     // qualified
                'ready',
                'dispatcher_price_set',
                'dispatcher_price_confirm',
                'picked',
            ])
            ->latest('orders.id')
            ->paginate(10, ['*'], 'active_page');

        $pendingCount = $pendingOrders->total();
        $activeCount  = $activeOrders->total();

        // Revenue today (delivered by me today)
        $today = Carbon::today();
        $revenueToday = Order::where('orders.dispatcher_id', $dispatcherId) // qualified
            ->where('orders.status', 'delivered')                           // qualified
            ->whereDate('orders.updated_at', $today)                        // qualified
            ->sum('orders.dispatcher_price');

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
        $countries = Country::all();
        return view('dispatcher.profile', compact('countries'));
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
            'country_id'    => ['nullable', 'string', 'exists:countries,id'],
            'address'    => ['nullable', 'string', 'max:255'],
        ]);

        $user->fill([
            'first_name'    => $data['first_name'],
            'last_name'     => $data['last_name'],
            'phone'   => $data['phone'] ?? null,
            'gender'  => $data['gender'] ?? null,
            'dob'     => $data['dob'] ?? null,
            'country_id' => $data['country_id'] ?? null,
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
