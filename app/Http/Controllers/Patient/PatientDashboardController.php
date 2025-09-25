<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Prescription;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Http\Request;

class PatientDashboardController extends Controller
{
    public function index(Request $r)
    {
        $userId = $r->user()->id;

        // Next accepted appointment in the future (video/chat/in_person)
        $pendingappointment = Appointment::where('patient_id', $userId)
            ->whereIn('status', ['pending'])
            ->count();

        $acceptedAppt = Appointment::with('doctor')
            ->where('patient_id', auth()->id())
            ->where('status', 'accepted')
            ->whereNotNull('meeting_link')
            ->orderByDesc('updated_at')
            ->first();
        // Active prescriptions
        $activeRxCount = Prescription::where('patient_id', $userId)
            ->where('status', 'active')->count();

        // Specialties for chips/filter
        $specialties = Specialty::orderBy('name')->get(['id', 'name', 'slug', 'icon', 'color']);

        // Nearby pharmacies count (if we have user lat/lng in session or on users table)
        $nearbyCount = 0;
        $lat = $r->user()->lat ?? session('lat');
        $lng = $r->user()->lng ?? session('lng');

        if ($lat && $lng) {
            // Within 10km radius using Haversine on users (role=pharmacist)
            $nearbyCount = User::pharmacists()
                ->whereNotNull('lat')->whereNotNull('lng')
                ->whereRaw("
            (6371 * acos(
                cos(radians(?)) * cos(radians(lat)) *
                cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat))
            )) <= 10
        ", [$lat, $lng, $lat])
                ->count();
        }

        return view('patient.dashboard', [
            'pendingappointment'   => $pendingappointment,
            'activeRxCount'  => $activeRxCount,
            'nearbyCount'    => $nearbyCount,
            'specialties'    => $specialties,
            'acceptedAppt'   => $acceptedAppt
        ]);
    }
}
