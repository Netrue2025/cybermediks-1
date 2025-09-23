<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Http\Request;

class AdminPrescriptionsController extends Controller
{
    public function index(Request $r)
    {
        $status = $r->query('status');
        $q      = trim((string)$r->query('q'));

        $rx = Prescription::with(['patient', 'doctor', 'pharmacy', 'items'])
            ->when($status, fn($w) => $w->where('dispense_status', $status))
            ->when($q !== '', function ($w) use ($q) {
                $w->where('code', 'like', "%$q%")
                    ->orWhereHas('patient', fn($p) => $p->where('first_name', 'like', "%$q%")->orWhere('last_name', 'like', "%$q%"))
                    ->orWhereHas('doctor', fn($d) => $d->where('first_name', 'like', "%$q%")->orWhere('last_name', 'like', "%$q%"));
            })
            ->latest()
            ->paginate(20);

        $pharmacies  = User::where('role', 'pharmacy')->orderBy('first_name')->get(['id', 'first_name', 'last_name']);
        $dispatchers = User::where('role', 'dispatcher')->orderBy('first_name')->get(['id', 'first_name', 'last_name']);

        return view('admin.prescriptions.index', compact('rx', 'status', 'q', 'pharmacies', 'dispatchers'));
    }

    public function reassignPharmacy(Request $r, Prescription $rx)
    {
        $r->validate(['pharmacy_id' => 'required|exists:users,id']);
        $pharmacy = User::where('id', $r->pharmacy_id)->where('role', 'pharmacy')->firstOrFail();
        $rx->pharmacy_id = $pharmacy->id;
        $rx->save();
        return back()->with('success', 'Pharmacy reassigned');
    }

    public function assignDispatcher(Request $r, Prescription $rx)
    {
        $r->validate(['dispatcher_id' => 'required|exists:users,id']);
        $dispatcher = User::where('id', $r->dispatcher_id)->where('role', 'dispatcher')->firstOrFail();
        $rx->dispatcher_id = $dispatcher->id;
        $rx->save();
        return back()->with('success', 'Dispatcher assigned');
    }

    // Admin-only force status (careful; obey minimal rules)
    public function forceStatus(Request $r, Prescription $rx)
    {
        $r->validate(['status' => 'required|in:pending,price_assigned,price_confirmed,ready,picked,cancelled']);
        $rx->dispense_status = (string) $r->input('status');
        $rx->save();
        return back()->with('success', 'Status changed');
    }
}
