<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentDispute;
use App\Models\Conversation;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\BalanceService;
use App\Services\DisputeHoldService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class PatientAppointmentController extends Controller
{
    public function create(Request $r)
    {
        $doctor = null;
        if ($r->filled('doctor_id')) {
            $doctor = User::where('role', 'doctor')->find($r->integer('doctor_id'));
        }
        return view('patient.appointments.create', compact('doctor'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'doctor_id'    => ['required', Rule::exists('users', 'id')->where('role', 'doctor')],
            'type'         => ['required', Rule::in(['video', 'chat', 'in_person'])],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
            'duration'     => ['nullable', 'integer', 'min:5', 'max:180'],
            'reason'       => ['nullable', 'string', 'max:255'],
        ]);

        // Get doctor and consult fee
        $doctor = User::where('role', 'doctor')->findOrFail($data['doctor_id']);
        $consultFee = (float)(optional($doctor->doctorProfile)->consult_fee ?? 0);

        // Check patient balance
        $patient = $r->user();
        $patientBalance = (float)($patient->wallet_balance ?? 0);

        if ($consultFee > 0 && $patientBalance < $consultFee) {
            return response()->json([
                'ok' => false,
                'error' => 'insufficient_balance',
                'message' => 'Insufficient balance. Please fund your account to continue.',
                'required_amount' => $consultFee,
                'current_balance' => $patientBalance,
            ], 422);
        }

        // Create appointment
        $appt = Appointment::create([
            'patient_id'    => $patient->id,
            'doctor_id'     => $data['doctor_id'],
            'type'          => $data['type'],
            'scheduled_at'  => $data['scheduled_at'] ?? null,
            'duration'      => $data['duration'] ?? null,
            'status'        => 'pending',
            'price'         => $consultFee,
            'payment_status' => 'paid',
            'reason'        => $data['reason'] ?? null,
        ]);

        // Process payment immediately after successful appointment creation
        if ($consultFee > 0) {
            try {
                // Create hold first
                BalanceService::holdAppointment($appt, $consultFee);
                // Immediately capture the hold (deduct from patient, credit doctor)
                BalanceService::captureHoldForAppointment($appt);
            } catch (\Exception $e) {
                Log::error('Payment processing failed for appointment: ' . $appt->id, [
                    'error' => $e->getMessage(),
                    'appointment_id' => $appt->id,
                ]);
                // Rollback appointment if payment fails
                $appt->delete();
                return response()->json([
                    'ok' => false,
                    'message' => 'Payment processing failed. Please try again.',
                ], 500);
            }
        }

        $redirect = route('patient.dashboard');

        if ($data['type'] === 'chat') {
            // check if there is a previous convo with the doctor
            $conversation = Conversation::where('patient_id', $patient->id)->where('doctor_id', $data['doctor_id'])->first();

            if (!$conversation) {
                $conversation = Conversation::create([
                    'patient_id' => $patient->id,
                    'doctor_id' => $data['doctor_id'],
                    'appointment_id' => $appt->id,
                    'status' => 'pending'
                ]);
            } else {
                $conversation->update(['status' => 'pending', 'appointment_id' => $appt->id]);
            }
            // $redirect = route('patient.messages') . '?c=' . $conversation->id;
            $redirect = route('patient.dashboard');
        }

        return response()->json([
            'ok' => true,
            'message' => 'Appointment request submitted.',
            'redirect' =>  $redirect
        ]);
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        $appointments = Appointment::query()
            ->with(['doctor:id,first_name,last_name', 'dispute'])
            ->forPatient($user->id)
            ->typeIs(match ($request->get('type')) {
                'Video' => 'video',
                'Chat' => 'chat',
                'In-person' => 'in_person',
                default => $request->get('type'),
            })
            ->onDate($request->get('date'))
            ->search($request->get('q'))
            ->orderByDesc('updated_at')
            ->paginate(10)
            ->withQueryString();

        if ($request->ajax()) {
            // Return HTML fragment for list only
            return view('patient.appointments._list', compact('appointments'))->render();
        }

        return view('patient.appointments.index', compact('appointments'));
    }

    public function close($id)
    {
        $user = Auth::user();

        $appointment = Appointment::with(['patient', 'doctor'])->where('id', $id)->where('patient_id', $user->id)->first();

        if (!$appointment) {
            return response()->json(['message' => 'Appointment not found'], 422);
        }

        if ($appointment->status !== 'accepted') {
            return response()->json(['message' => 'Only accepted requests can be closed'], 422);
        }

        $appointment->status = 'completed';
        $appointment->save();

        $patient = $appointment->patient;
        $doctor = $appointment->doctor;
        $doctorProfile = $doctor->doctorProfile;
        $fee = $doctorProfile?->consult_fee;
        if ($fee && $patient) {
            // Charge the patient
            $patient->wallet_balance -= $fee;
            $patient->save();

            // Pay the doctor
            $doctor->wallet_balance += $fee;
            $doctor->save();

            // Log the transaction (pseudo-code, implement as needed)
            WalletTransaction::create([
                'user_id' => $patient->id,
                'amount' => -$fee,
                'currency' => 'NGN',
                'type' => 'debit',
                'reference' => uniqid('txn_'),
                'purpose' => "Consultation fee for appointment ID {$appointment->id}",
            ]);

            WalletTransaction::create([
                'user_id' => $doctor->id,
                'amount' => $fee,
                'currency' => 'NGN',
                'type' => 'credit',
                'reference' => uniqid('txn_'),
                'purpose' => "Consultation fee received for appointment ID {$appointment->id}",
            ]);
        }

        return response()->json(['ok' => true, 'message' => 'Appointment closed']);
    }

    public function storeDispute(Request $request, Appointment $appointment)
    {
        // Ensure the logged-in user owns this appointment as patient
        if ((int)$appointment->patient_id !== (int)Auth::id()) {
            abort(403, 'You cannot dispute this appointment.');
        }

        // Block double disputes (if you track it)
        if ($appointment->status === 'disputed' || $appointment->dispute()->exists()) {
            return back()->withErrors(['dispute' => 'This appointment is already disputed.']);
        }

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:2000'],
        ]);

        // Create the dispute record
        $dispute = AppointmentDispute::create([
            'appointment_id' => $appointment->id,
            'patient_id'     => Auth::id(),
            'reason'         => $data['reason'],
            'status'         => 'open', // open -> admin_review -> resolved
        ]);

        // Flip appointment status to disputed (so downstream logic can hold funds, etc.)
        $appointment->update(['status' => 'disputed']);
        $amount = (float) $appointment->price;
        DisputeHoldService::openDoctorHold(
            $appointment,
            $amount,
            $appointment->patient_id,
            $appointment->doctor_id,
            $dispute->id
        );

        return back()->with('ok', 'Dispute opened. Our team will review shortly.');
    }
}
