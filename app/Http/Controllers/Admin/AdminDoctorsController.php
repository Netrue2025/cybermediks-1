<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DoctorCredential;
use App\Models\User;
use Illuminate\Http\Request;

class AdminDoctorsController extends Controller
{
    public function index(Request $r)
    {
        $q        = trim((string) $r->query('q', ''));
        $country  = trim((string) $r->query('country', ''));

        // For a dropdown of countries that actually have doctors
        $countries = User::where('role', 'doctor')
            ->whereNotNull('country')
            ->select('country')->distinct()
            ->orderBy('country')
            ->pluck('country');

        $doctors = User::with([
            'doctorProfile:id,doctor_id,is_available,title,consult_fee,avg_duration',
            'specialties'
        ])
            ->where('role', 'doctor')
            ->when($q !== '', function ($w) use ($q) {
                $w->where(function ($x) use ($q) {
                    $x->where('first_name', 'like', "%{$q}%")
                        ->orWhere('last_name', 'like', "%{$q}%")
                        ->orWhereHas('doctorProfile', fn($p) => $p->where('title', 'like', "%{$q}%"));
                    // Optionally search specialties too:
                    // ->orWhereHas('specialties', fn ($s) => $s->where('name', 'like', "%{$q}%"));
                });
            })
            // COUNTRY FILTER (case-insensitive exact match)
            ->when($country !== '', function ($w) use ($country) {
                $w->whereRaw('LOWER(country) = ?', [strtolower($country)]);
                // or use LIKE if you want partial matches:
                // $w->where('country', 'like', "%{$country}%");
            })
            ->orderBy('first_name')
            ->paginate(20)
            ->withQueryString();
        $credentials = DoctorCredential::with('doctor')->where('status', 'pending')->latest()->take(10)->get();

        return view('admin.doctors.index', compact('doctors', 'q', 'credentials', 'country', 'countries'));
    }


    public function availability($id)
    {
        $doc = User::where('role', 'doctor')->with('doctorProfile')->findOrFail($id);
        $doc->doctorProfile?->update(['is_available' => ! (bool) $doc->doctorProfile->is_available]);
        return back()->with('success', 'Availability toggled');
    }

    public function approveCredential(Request $r, $id)
    {
        // Expecting ->input('credential_id')
        $credId = (int) $r->input('credential_id');
        $cred = DoctorCredential::where('doctor_id', $id)->findOrFail($credId);
        $cred->update(['status' => 'approved']); // requires status column
        return back()->with('success', 'Credential approved');
    }

    public function credentials(User $doctor)
    {
        // authorize admin as you already do
        $docs = DoctorCredential::where('doctor_id', $doctor->id)
            ->orderByDesc('created_at')
            ->get();

        return view('admin.doctors._credentials', compact('doctor', 'docs'));
    }
}
