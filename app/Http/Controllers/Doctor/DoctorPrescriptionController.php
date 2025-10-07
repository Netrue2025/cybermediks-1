<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Conversation;
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
        $patient = User::where('role', 'patient')->where('id', $request->patient_id)->first();

        if (!$patient) {
            return back()->with('error', 'Patient not found');
        }

        return view('doctor.prescriptions.create', compact('patient'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'patient_id'         => ['required', 'integer', 'exists:users,id'],
            'appointment_id'     => ['required', 'integer', 'exists:appointments,id'],
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

        $appointment = Appointment::where('doctor_id', $doctorId)->where('patient_id', $data['patient_id'])->where('id', $data['appointment_id'])->first();

        if (!$appointment)
        {
            return response()->json(['message' => 'You cannont add a prescription for this appointment'], 422);
        }
        

        if ($appointment->type === 'chat')
        {
            // check if conversation has been closed
            $convo = Conversation::where('appointment_id', $appointment->id)->where('doctor_id', $doctorId)->where('patient_id', $data['patient_id'])->first();
            if (!$convo)
            {
                return response()->json(['message' => 'You cannont add a prescription for this appointment, no conversation found'], 422);
            }

            if ($convo->status === 'closed')
            {
                return response()->json(['message' => 'You cannont add a prescription for this conversation, it has been closed already.'], 422);
            }

        }

        // Create parent prescription
        $rx = Prescription::create([
            'appointment_id' => $data['appointment_id'],  // or pass one if creating from appointment
            'patient_id'     => $data['patient_id'],
            'doctor_id'      => $doctorId,
            'code'           => 'RX-' . now()->format('Y') . '-' . str_pad((string) (Prescription::max('id') + 1), 6, '0', STR_PAD_LEFT),
            'status'         => 'active',
            'notes'          => $data['notes'] ?? null,
            'encounter'      => $data['encounter'],
            'refills'        => (int)($data['refills'] ?? 0),
            'dispense_status' => ''
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

        $appointment->update(['prescription_issued' => true]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Prescription issued',
            'redirect' => route('patient.prescriptions.index'), // or a doctor-side show page
            'rx_id'   => $rx->id,
        ]);
    }

    protected function baseQuery()
    {
        return Prescription::query()
            ->with([
                'patient:id,first_name,last_name,email',
                'items:id,prescription_id,drug,dose,frequency,days,quantity',
            ]);
    }

    public function index(Request $request)
    {
        $doctorId = Auth::id();

        $q        = (string) $request->query('q', '');
        $dateFrom = $request->query('from', '');
        $dateTo   = $request->query('to', '');
        $status   = (string) $request->query('status', ''); // optional: pending/ready/picked/cancelled

        $rx = $this->baseQuery()->where('doctor_id', $doctorId);

        if ($q !== '') {
            $rx->where(function ($w) use ($q) {
                $w->where('code', 'like', "%{$q}%")
                    ->orWhereHas('patient', fn($p) => $p->where('first_name', 'like', "%{$q}%")
                        ->orWhere('last_name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%"))
                    ->orWhereHas('items', fn($i) => $i->where('drug', 'like', "%{$q}%")
                        ->orWhere('drug', 'like', "%{$q}%"));
            });
        }

        if (in_array($status, ['pending', 'ready', 'picked', 'cancelled'])) {
            $rx->where('dispense_status', $status);
        }

        if ($dateFrom) $rx->whereDate('created_at', '>=', $dateFrom);
        if ($dateTo)   $rx->whereDate('created_at', '<=', $dateTo);

        $prescriptions = $rx->orderByDesc('created_at')->paginate(12)->withQueryString();

        if ($request->ajax()) {
            return view('doctor.prescriptions._list', compact('prescriptions'))->render();
        }

        return view('doctor.prescriptions.index', compact('prescriptions', 'q', 'dateFrom', 'dateTo', 'status'));
    }

    public function show(Prescription $rx)
    {
        abort_unless($rx->doctor_id === Auth::id(), 403);
        $rx->loadMissing('patient', 'items');
        return view('doctor.prescriptions.show', compact('rx'));
    }
}
