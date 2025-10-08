<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminDispatchersController extends Controller
{
    public function index(Request $r)
    {
        $q       = trim((string) $r->query('q', ''));
        $country = trim((string) $r->query('country', ''));

        // Countries that actually have dispatchers
        $countries = User::where('role', 'dispatcher')
            ->whereNotNull('country')
            ->select('country')->distinct()
            ->orderBy('country')
            ->pluck('country');

        $dispatchers = User::where('role', 'dispatcher')
            ->when($q !== '', function ($w) use ($q) {
                $w->where(function ($x) use ($q) {
                    $x->where('first_name', 'like', "%{$q}%")
                        ->orWhere('last_name',  'like', "%{$q}%")
                        ->orWhere('email',      'like', "%{$q}%");
                });
            })
            // COUNTRY FILTER (case-insensitive exact match)
            ->when($country !== '', function ($w) use ($country) {
                $w->whereRaw('LOWER(country) = ?', [strtolower($country)]);
                // or partial match:
                // $w->where('country', 'like', "%{$country}%");
            })
            ->orderBy('first_name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.dispatchers.index', compact('dispatchers', 'q', 'country', 'countries'));
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
