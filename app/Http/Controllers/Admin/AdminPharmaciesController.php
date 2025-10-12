<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PharmacyProfile;
use App\Models\User;
use Illuminate\Http\Request;

class AdminPharmaciesController extends Controller
{
    public function index(Request $r)
    {
        $q           = trim((string) $r->query('q', ''));
        $countryId   = $r->query('country_id');                 // preferred FK filter
        $countryName = trim((string) $r->query('country', '')); // legacy text filter (optional)

        // Return ALL countries for the dropdown
        $countries = \App\Models\Country::orderBy('name')->get(['id', 'name', 'iso2']);

        $pharmacies = \App\Models\User::with([
            'pharmacyProfile',
            'country:id,name,iso2', // optional, to show country name/flag
        ])
            ->where('role', 'pharmacy')

            // search
            ->when($q !== '', function ($w) use ($q) {
                $like = "%{$q}%";
                $w->where(function ($x) use ($like) {
                    $x->where('first_name', 'like', $like)
                        ->orWhere('last_name',  'like', $like)
                        ->orWhere('email',      'like', $like);
                });
            })

            // preferred: filter by country FK
            ->when(filled($countryId), fn($w) => $w->where('country_id', $countryId))

            // legacy: accept free-text country name or ISO2 if no country_id provided
            ->when($countryName !== '' && empty($countryId), function ($w) use ($countryName) {
                $needle = strtolower($countryName);
                $w->where(function ($x) use ($needle) {
                    // if you have users.country_id + relation
                    $x->whereHas('country', function ($c) use ($needle) {
                        $c->whereRaw('LOWER(name) = ?', [$needle])
                            ->orWhereRaw('LOWER(iso2) = ?', [$needle]);
                    });

                    // if you still keep a legacy users.country text column, you can also honor it:
                    // ->orWhereRaw('LOWER(country) = ?', [$needle]);
                });
            })

            ->orderBy('first_name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.pharmacies.index', [
            'pharmacies' => $pharmacies,
            'q'          => $q,
            'country'    => $countryName, // keep legacy param if your form still has it
            'countryId'  => $countryId,
            'countries'  => $countries,   // **all** countries for the dropdown
        ]);
    }



    public function profile(User $pharmacy)
    {
        // ensure it's a pharmacy
        if ($pharmacy->role !== 'pharmacy') {
            abort(404);
        }

        $profile = PharmacyProfile::where('user_id', $pharmacy->id)->first();

        return view('admin.pharmacies._profile', compact('pharmacy', 'profile'));
    }

    // Toggle 24/7
    public function toggle24(User $pharmacy)
    {
        if ($pharmacy->role !== 'pharmacy') abort(404);

        $profile = PharmacyProfile::firstOrCreate(['user_id' => $pharmacy->id]);
        $profile->is_24_7 = ! (bool) ($profile->is_24_7 ?? false);
        $profile->save();

        return back()->with('status', 'Updated 24/7 availability');
    }

    // Update radius
    public function updateRadius(Request $request, User $pharmacy)
    {
        if ($pharmacy->role !== 'pharmacy') abort(404);

        $data = $request->validate([
            'delivery_radius_km' => 'nullable|numeric|min:0|max:1000',
        ]);

        $profile = PharmacyProfile::firstOrCreate(['user_id' => $pharmacy->id]);
        $profile->delivery_radius_km = $data['delivery_radius_km'] ?? null;
        $profile->save();

        // For AJAX form we can return a simple 200
        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok']);
        }
        return back()->with('status', 'Radius updated');
    }
}
