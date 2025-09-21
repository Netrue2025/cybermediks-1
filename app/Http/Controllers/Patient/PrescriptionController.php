<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrescriptionController extends Controller
{
     public function index(Request $request)
    {
        $user = Auth::user();

        $status = match (strtolower((string)$request->get('status'))) {
            'active' => 'active',
            'expired' => 'expired',
            'refill requested', 'refill_requested' => 'refill_requested',
            default => null
        };

        $prescriptions = Prescription::query()
            ->with(['doctor:id,first_name,last_name','items'])
            ->forPatient($user->id)
            ->statusIs($status)
            ->search($request->get('q'))
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        if ($request->ajax()) {
            return view('patient.prescriptions._list', compact('prescriptions'))->render();
        }

        return view('patient.prescriptions.index', compact('prescriptions'));
    }
}
