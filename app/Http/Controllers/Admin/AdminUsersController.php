<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUsersController extends Controller
{
    public function index(Request $r)
    {
        $q        = trim((string) $r->query('q', ''));
        $role     = $r->query('role');          // optional, if you still want it
        $country  = trim((string) $r->query('country', '')); // NEW

        // For a dropdown of available countries (from patients only)
        $countries = User::where('role', 'patient')
            ->whereNotNull('country')
            ->select('country')->distinct()
            ->orderBy('country')
            ->pluck('country')
            ->map(fn($c) => strtoupper($c));

        $users = User::query()
            ->where('role', 'patient')
            ->when($role, fn($w) => $w->where('role', $role))
            ->when($q !== '', function ($w) use ($q) {
                $w->where(function ($x) use ($q) {
                    $x->where('first_name', 'like', "%$q%")
                        ->orWhere('last_name', 'like', "%$q%")
                        ->orWhere('email', 'like', "%$q%");
                });
            })
            // FILTER BY COUNTRY (case-insensitive exact match)
            ->when($country !== '', function ($w) use ($country) {
                $w->whereRaw('LOWER(country) = ?', [strtolower($country)]);
            })
            ->orderBy('first_name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'q', 'role', 'country', 'countries'));
    }



    public function toggleActive(User $user)
    {
        $user->is_active = ! (bool)$user->is_active;
        $user->save();
        return back()->with('success', 'User status updated');
    }
}
