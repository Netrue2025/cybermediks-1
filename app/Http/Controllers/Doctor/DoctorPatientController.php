<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\LabworkRequest;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DoctorPatientController extends Controller
{
    public function index(Request $request)
    {
        $docId = Auth::id();
        $q     = trim((string) $request->get('q'));
        $flt   = strtolower((string) $request->get('filter')); // "", "recent", "with active rx", "follow-ups"

        // Base: patients who have at least one appointment with this doctor
        $base = User::query()
            ->select([
                'users.id',
                'users.first_name',
                'users.last_name',
                'users.email',
                // total visits with this doctor (completed or past scheduled)
                DB::raw("(
                    SELECT COUNT(*)
                    FROM appointments a
                    WHERE a.patient_id = users.id
                      AND a.doctor_id = {$docId}
                ) as visits_count"),
                // most recent appointment datetime
                DB::raw("(
                    SELECT MAX(a2.scheduled_at)
                    FROM appointments a2
                    WHERE a2.patient_id = users.id
                      AND a2.doctor_id = {$docId}
                ) as last_visit_at"),
                // has active prescription with this doctor
                DB::raw("EXISTS(
                    SELECT 1 FROM prescriptions p
                    WHERE p.patient_id = users.id
                      AND p.doctor_id = {$docId}
                      AND p.status = 'active'
                ) as has_active_rx"),
                // has upcoming appointment (follow-up)
                DB::raw("EXISTS(
                    SELECT 1 FROM appointments a3
                    WHERE a3.patient_id = users.id
                      AND a3.doctor_id = {$docId}
                      AND a3.status = 'scheduled'
                      AND a3.scheduled_at > NOW()
                ) as has_followup"),
            ])
            ->whereExists(function ($sub) use ($docId) {
                $sub->from('appointments as ap')
                    ->whereColumn('ap.patient_id', 'users.id')
                    ->where('ap.doctor_id', $docId);
            });

        // Search: name / email / ID
        if ($q !== '') {
            $base->where(function ($w) use ($q) {
                $w->where('users.first_name', 'like', "%{$q}%")
                    ->orWhere('users.last_name', 'like', "%{$q}%")
                    ->orWhere('users.email', 'like', "%{$q}%")
                    ->orWhere('users.id', $q);
            });
        }

        // Filter
        switch ($flt) {
            case 'recent':
                // last 30 days
                $base->whereRaw("(SELECT MAX(a2.scheduled_at) FROM appointments a2 WHERE a2.patient_id=users.id AND a2.doctor_id=?) >= DATE_SUB(NOW(), INTERVAL 30 DAY)", [$docId]);
                break;
            case 'with active rx':
                $base->whereRaw("EXISTS(SELECT 1 FROM prescriptions p WHERE p.patient_id=users.id AND p.doctor_id=? AND p.status='active')", [$docId]);
                break;
            case 'follow-ups':
                $base->whereRaw("EXISTS(SELECT 1 FROM appointments a3 WHERE a3.patient_id=users.id AND a3.doctor_id=? AND a3.status='scheduled' AND a3.scheduled_at>NOW())", [$docId]);
                break;
        }

        $patients = $base
            // ->orderByDesc(DB::raw('last_visit_at'))
            ->orderByDesc('updated_at')
            ->paginate(12)
            ->withQueryString();

        if ($request->ajax()) {
            return view('doctor.patients._list', compact('patients'))->render();
        }

        return view('doctor.patients.index', compact('patients'));
    }

    public function show(Request $request, User $patient)
    {
        $docId = Auth::id();

        $hasRelation = Appointment::where('doctor_id', $docId)
            ->where('patient_id', $patient->id)
            ->exists();

        abort_unless($hasRelation, 403, 'You do not have access to this patient.');

        // Stats
        $stats = Appointment::selectRaw('
            COUNT(*) as total_visits,
            SUM(CASE WHEN status="scheduled" AND scheduled_at > NOW() THEN 1 ELSE 0 END) as upcoming_count,
            MIN(scheduled_at) as first_visit_at,
            MAX(scheduled_at) as last_visit_at
        ')
            ->where('doctor_id', $docId)
            ->where('patient_id', $patient->id)
            ->first();

        $activeRxCount = Prescription::where('doctor_id', $docId)
            ->where('patient_id', $patient->id)
            ->where('status', 'active')
            ->count();

        // Lists
        $appointments = Appointment::with([])
            ->where('doctor_id', $docId)
            ->where('patient_id', $patient->id)
            ->orderByDesc('scheduled_at')
            ->paginate(10, ['*'], 'ap_page')
            ->withQueryString();

        $prescriptions = Prescription::with(['items'])
            ->where('doctor_id', $docId)
            ->where('patient_id', $patient->id)
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'rx_page')
            ->withQueryString();

        // NEW: Labworks for this patient (regardless of labtech)
        $labworks = LabworkRequest::with(['labtech']) // ensure relation exists: belongsTo(User::class,'labtech_id')
            ->where('patient_id', $patient->id)
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'lab_page')
            ->withQueryString();

        return view('doctor.patients.history', [
            'patient'        => $patient,
            'stats'          => $stats,
            'activeRxCount'  => $activeRxCount,
            'appointments'   => $appointments,
            'prescriptions'  => $prescriptions,
            'labworks'       => $labworks, // NEW
        ]);
    }
}
