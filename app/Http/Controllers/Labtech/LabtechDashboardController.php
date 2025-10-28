<?php

namespace App\Http\Controllers\Labtech;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\LabworkRequest;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class LabtechDashboardController extends Controller
{
    public function index(Request $request)
    {
        $me = Auth::id();

        // Metrics
        $pendingCount = LabworkRequest::where('labtech_id', $me)
            ->where('status', 'pending')->count();

        $activeCount = LabworkRequest::where('labtech_id', $me)
            ->whereIn('status', ['accepted', 'scheduled', 'in_progress', 'results_uploaded'])
            ->count();

        $completedToday = LabworkRequest::where('labtech_id', $me)
            ->where('status', 'completed')
            ->whereDate('updated_at', now()->toDateString())
            ->count();

        $revenueToday = LabworkRequest::where('labtech_id', $me)
            ->where('status', 'completed')
            ->whereDate('updated_at', now()->toDateString())
            ->sum('price');

        // Lists (latest 8)
        $pending = LabworkRequest::with(['patient:id,first_name,last_name'])
            ->where('labtech_id', $me)
            ->where('status', 'pending')
            ->latest()->take(8)->get();

        $active = LabworkRequest::with(['patient:id,first_name,last_name'])
            ->where('labtech_id', $me)
            ->whereIn('status', ['accepted', 'scheduled', 'in_progress', 'results_uploaded'])
            ->latest()->take(8)->get();

        return view('labtech.dashboard', compact(
            'pendingCount',
            'activeCount',
            'completedToday',
            'revenueToday',
            'pending',
            'active'
        ));
    }

    public function walletIndex(Request $request)
    {
        $user = $request->user();
        $transactions = WalletTransaction::forUser($user->id)->latestFirst()->paginate(10)->withQueryString();
        $balance = $user->wallet_balance;
        $fee = (23 / 100);

        if ($request->ajax()) {
            return view('labtech.wallet._list', compact('transactions'))->render();
        }

        return view('labtech.wallet.index', compact('balance', 'transactions', 'fee'));
    }

    public function showProfile()
    {
        $countries = Country::all();
        return view('labtech.profile', compact('countries'));
    }

    public function updateProfile(Request $r)
    {
        $user = $r->user();

        $data = $r->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['nullable', 'string', 'max:100'],
            'phone'      => ['nullable', 'string', 'max:40'],
            'facility_name' => ['nullable', 'string', 'max:255'],
            'gender'     => ['nullable', 'in:male,female,other'],
            'dob'        => ['nullable', 'date'],
            'country_id'    => ['nullable', 'string', 'exists:countries,id'],
            'address'    => ['nullable', 'string', 'max:255'],
            'address_building_no'    => ['nullable', 'string', 'max:100'],
            'state'    => ['nullable', 'string', 'max:100'],
            'city'    => ['nullable', 'string', 'max:100'],
        ]);

        $user->fill([
            'first_name'    => $data['first_name'],
            'last_name'     => $data['last_name'],
            'phone'   => $data['phone'] ?? null,
            'facility_name' => $data['facility_name'] ?? null,
            'gender'  => $data['gender'] ?? null,
            'dob'     => $data['dob'] ?? null,
            'country_id' => $data['country_id'] ?? null,
            'state' => $data['state'] ?? null,
            'city' => $data['city'] ?? null,
            'address' => $data['address'] ?? null,
            'address_building_no' => $data['address_building_no'] ?? null,
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
