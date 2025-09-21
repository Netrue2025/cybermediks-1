<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PatientAppointmentController extends Controller
{
    public function create(Request $r)
    {
        $doctor = null;
        if ($r->filled('doctor_id')) {
            $doctor = User::where('role','doctor')->find($r->integer('doctor_id'));
        }
        return view('patient.appointments.create', compact('doctor'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'doctor_id'    => ['required', Rule::exists('users','id')->where('role','doctor')],
            'type'         => ['required', Rule::in(['video','chat','in_person'])],
            'scheduled_at' => ['nullable','date','after:now'],
            'duration'     => ['nullable','integer','min:5','max:180'],
            'reason'       => ['nullable','string','max:255'],
        ]);

        // Price can be dynamic (doctor_profiles.consult_fee) — simple fetch here:
        $doctor = User::where('role','doctor')->findOrFail($data['doctor_id']);
        $price  = optional($doctor->doctorProfile)->consult_fee ?? 0;

        $appt = Appointment::create([
            'patient_id'    => $r->user()->id,
            'doctor_id'     => $data['doctor_id'],
            'type'          => $data['type'],
            'scheduled_at'  => $data['scheduled_at'] ?? null,
            'duration'      => $data['duration'] ?? null,
            'status'        => 'pending',
            'price'         => $price,
            'payment_status'=> 'unpaid',
            'reason'        => $data['reason'] ?? null,
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Appointment request submitted.',
            'redirect' => route('patient.messages').'?doctor='.$doctor->id, // nudge to chat while waiting, tweak as you like
        ]);
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        $appointments = Appointment::query()
            ->with(['doctor:id,first_name,last_name'])
            ->forPatient($user->id)
            ->typeIs(match ($request->get('type')) {
                'Video' => 'video',
                'Chat' => 'chat',
                'In-person' => 'in_person',
                default => $request->get('type'),
            })
            ->onDate($request->get('date'))
            ->search($request->get('q'))
            ->orderByDesc('scheduled_at')
            ->paginate(10)
            ->withQueryString();

        if ($request->ajax()) {
            // Return HTML fragment for list only
            return view('patient.appointments._list', compact('appointments'))->render();
        }

        return view('patient.appointments.index', compact('appointments'));
    }
}
