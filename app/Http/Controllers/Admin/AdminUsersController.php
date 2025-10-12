<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUsersController extends Controller
{
    public function index(Request $r)
    {
        $q = trim((string) $r->query('q', ''));
        $rawRole = $r->query('role');                 // optional
        $countryId = $r->query('country_id');         // preferred filter (FK)
        $countryName = trim((string) $r->query('country', '')); // legacy text filter (optional)

        // Optional: constrain role to a known set; default to 'patient'
        $allowedRoles = ['patient', 'doctor', 'pharmacy', 'dispatcher', 'admin'];
        $role = in_array($rawRole, $allowedRoles, true) ? $rawRole : 'patient';

        // For a dropdown in the UI
        $countries = Country::orderBy('name')->get(['id', 'name', 'iso2']);

        $users = User::query()
            ->with(['country:id,name,iso2'])                 // if you have a belongsTo relation `country()`
            ->where('role', $role)
            ->when($q !== '', function ($w) use ($q) {
                $like = "%{$q}%";
                $w->where(function ($x) use ($like) {
                    $x->where('first_name', 'like', $like)
                        ->orWhere('last_name',  'like', $like)
                        ->orWhere('email',      'like', $like)
                        ->orWhere('phone',      'like', $like); // optional if you store phone
                });
            })
            // Preferred: filter by foreign key
            ->when(filled($countryId), fn($w) => $w->where('country_id', $countryId))

            // Legacy fallback: if you still have a plain text `country` column OR want name-based matching
            ->when($countryName !== '' && empty($countryId), function ($w) use ($countryName) {
                $w->where(function ($x) use ($countryName) {
                    // If you have a countries table + relation:
                    $x->whereHas(
                        'country',
                        fn($c) =>
                        $c->whereRaw('LOWER(name) = ?', [strtolower($countryName)])
                            ->orWhereRaw('LOWER(iso2) = ?', [strtolower($countryName)])
                    );
                    // If you still keep a legacy users.country text column, uncomment:
                    // ->orWhereRaw('LOWER(country) = ?', [strtolower($countryName)]);
                });
            })

            ->orderBy('first_name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', [
            'users'      => $users,
            'q'          => $q,
            'role'       => $role,
            'countryId'  => $countryId,
            'country'    => $countryName, // for the legacy input, if you keep it in the form
            'countries'  => $countries,
        ]);
    }




    public function toggleActive(User $user)
    {
        $user->is_active = ! (bool)$user->is_active;
        $user->save();
        return back()->with('success', 'User status updated');
    }
}
