<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\DoctorCredential;
use App\Models\User;
use Illuminate\Http\Request;

class AdminDoctorsController extends Controller
{
    public function index(Request $r)
    {
        $q           = trim((string) $r->query('q', ''));
        $countryId   = $r->query('country_id');                 // preferred (FK)
        $countryName = trim((string) $r->query('country', '')); // legacy text filter (optional)

        // Countries that actually have doctors (for the dropdown)
        $countries = Country::get(['id', 'name', 'iso2']);

        $doctors = User::with([
            'doctorProfile:id,doctor_id,is_available,title,consult_fee,avg_duration',
            'specialties',
            'country:id,name,iso2', // optional, if you want to show country names/flags
        ])
            ->where('role', 'doctor')

            // search by name/title (/optionally specialties)
            ->when($q !== '', function ($w) use ($q) {
                $like = "%{$q}%";
                $w->where(function ($x) use ($like) {
                    $x->where('first_name', 'like', $like)
                        ->orWhere('last_name',  'like', $like)
                        ->orWhereHas('doctorProfile', fn($p) => $p->where('title', 'like', $like));
                    // ->orWhereHas('specialties', fn ($s) => $s->where('name', 'like', $like));
                });
            })

            // preferred: filter by country FK
            ->when(filled($countryId), fn($w) => $w->where('country_id', $countryId))

            // legacy: accept free-text country name or ISO2 if no country_id provided
            ->when($countryName !== '' && empty($countryId), function ($w) use ($countryName) {
                $needle = strtolower($countryName);
                $w->where(function ($x) use ($needle) {
                    $x->whereHas('country', function ($c) use ($needle) {
                        $c->whereRaw('LOWER(name) = ?', [$needle])
                            ->orWhereRaw('LOWER(iso2) = ?', [$needle]);
                    });

                    // if you still have a legacy users.country string column and want to honor it, uncomment:
                    // ->orWhereRaw('LOWER(country) = ?', [$needle]);
                });
            })

            ->orderBy('first_name')
            ->paginate(20)
            ->withQueryString();

        // Pending credentials (scope to chosen country if provided)
        $credentials = \App\Models\DoctorCredential::with('doctor')
            ->where('status', 'pending')
            ->when(
                filled($countryId),
                fn($q2) =>
                $q2->whereHas('doctor', fn($d) => $d->where('country_id', $countryId))
            )
            ->latest()
            ->take(10)
            ->get();

        return view('admin.doctors.index', [
            'doctors'     => $doctors,
            'q'           => $q,
            'credentials' => $credentials,
            'country'     => $countryName,   // keep legacy param for the form if you still show it
            'countryId'   => $countryId,
            'countries'   => $countries,
        ]);
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
