<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminDispatchersController extends Controller
{
    public function index(Request $r)
    {
        $q           = trim((string) $r->query('q', ''));
        $countryId   = $r->query('country_id');                 // preferred FK filter
        $countryName = trim((string) $r->query('country', '')); // legacy text filter (optional)

        // Return ALL countries for the dropdown
        $countries = \App\Models\Country::orderBy('name')->get(['id', 'name', 'iso2']);

        $dispatchers = \App\Models\User::with(['country:id,name,iso2']) // optional: show country label/flag
            ->where('role', 'dispatcher')

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
                    $x->whereHas('country', function ($c) use ($needle) {
                        $c->whereRaw('LOWER(name) = ?', [$needle])
                            ->orWhereRaw('LOWER(iso2) = ?', [$needle]);
                    });

                    // if you still have users.country (text) and want to honor it, uncomment:
                    // ->orWhereRaw('LOWER(country) = ?', [$needle]);
                });
            })

            ->orderBy('first_name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.dispatchers.index', [
            'dispatchers' => $dispatchers,
            'q'           => $q,
            'country'     => $countryName, // keep legacy param if your form still has it
            'countryId'   => $countryId,
            'countries'   => $countries,   // ALL countries for the dropdown
        ]);
    }



    public function profile(User $dispatcher)
    {
        if ($dispatcher->role !== 'dispatcher') abort(404);

        // If you track delivery stats, compute here; otherwise zeros.
        $stats = [
            'pending'   => 0,
            'active'    => 0,
            'completed' => 0,
        ];

        return view('admin.dispatchers._profile', compact('dispatcher', 'stats'));
    }
}
