<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminsController extends Controller
{
    public function index()
    {
        $admins = User::where('role', 'admin')->orderBy('first_name')->paginate(20);
        return view('admin.admins.index', compact('admins'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'first_name' => 'required|max:120',
            'last_name' => 'required|max:120',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|min:8'
        ]);

        User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email'     => $data['email'],
            'password'  => bcrypt($data['password']),
            'role'      => 'admin',
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        return back()->with('success', 'Admin created');
    }

    public function destroy($id)
    {
        $admin = User::where('role', 'admin')->findOrFail($id);
        if (auth()->id() === $admin->id) {
            return back()->with('danger', 'You cannot delete yourself.');
        }
        $admin->delete();
        return back()->with('success', 'Admin removed');
    }
}
