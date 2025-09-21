<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DoctorPrescriptionController extends Controller
{
    public function create(Request $request)
    {
        // Load patients you’re allowed to prescribe for.
        // Adjust the query if you have a dedicated patient-doctor relation.
        $patients = User::query()
            ->where('role', 'patient') // if you use role column; otherwise adapt
            ->orderBy('first_name')
            ->limit(200)
            ->get(['id', 'first_name', 'last_name']);

        return view('doctor.prescriptions.create', compact('patients'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'patient_id'         => ['required', 'integer', 'exists:users,id'],
            'encounter'          => ['required', Rule::in(['video', 'chat', 'in_person'])],
            'refills'            => ['nullable', 'integer', 'min:0'],
            'notes'              => ['nullable', 'string', 'max:255'],

            'items'              => ['required', 'array', 'min:1'],
            'items.*.drug'       => ['required', 'string', 'max:255'],
            'items.*.dose'       => ['nullable', 'string', 'max:100'],
            'items.*.freq'       => ['nullable', 'string', 'max:100'],
            'items.*.days'       => ['nullable', 'string', 'max:50'],
            'items.*.quantity'        => ['nullable', 'integer', 'min:1'],
        ]);

        $doctorId = Auth::id();

        // Create parent prescription
        $rx = Prescription::create([
            'appointment_id' => null,  // or pass one if creating from appointment
            'patient_id'     => $data['patient_id'],
            'doctor_id'      => $doctorId,
            'code'           => 'RX-' . now()->format('Y') . '-' . str_pad((string) (Prescription::max('id') + 1), 6, '0', STR_PAD_LEFT),
            'status'         => 'active',
            'notes'          => $data['notes'] ?? null,
            'encounter'      => $data['encounter'],
            'refills'        => (int)($data['refills'] ?? 0),
        ]);

        // Map items
        $itemsPayload = [];
        foreach ($data['items'] as $i) {
            $itemsPayload[] = [
                'drug'    => $i['drug'],
                'dose'      => $i['dose'] ?? null,
                'frequency'   => $i['freq'] ?? null,
                'days'    => $i['days'] ?? null,
                'quantity'         => $i['quantity'] ?? null,
                'directions' => null,
            ];
        }
        $rx->items()->createMany($itemsPayload);

        return response()->json([
            'status'  => 'success',
            'message' => 'Prescription issued',
            'redirect' => route('patient.prescriptions.index'), // or a doctor-side show page
            'rx_id'   => $rx->id,
        ]);
    }
}
