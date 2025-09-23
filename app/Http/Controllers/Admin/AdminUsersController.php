<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUsersController extends Controller
{
    public function index(Request $r)
    {
        $q = trim((string)$r->query('q'));
        $role = $r->query('role');

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
            ->orderBy('first_name')
            ->paginate(20);

        return view('admin.users.index', compact('users', 'q', 'role'));
    }

    public function toggleActive(User $user)
    {
        $user->is_active = ! (bool)$user->is_active;
        $user->save();
        return back()->with('success', 'User status updated');
    }
}
