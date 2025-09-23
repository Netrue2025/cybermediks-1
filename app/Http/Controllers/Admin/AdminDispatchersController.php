<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminDispatchersController extends Controller
{
    public function index(Request $r)
    {
        $q = trim((string) $r->query('q'));

        $dispatchers = User::where('role', 'dispatcher')
            ->when($q !== '', function ($w) use ($q) {
                $w->where(function ($x) use ($q) {
                    $x->where('first_name', 'like', "%$q%")
                        ->orWhere('last_name', 'like', "%$q%")
                        ->orWhere('email', 'like', "%$q%");
                });
            })
            ->orderBy('first_name')
            ->paginate(20);

        return view('admin.dispatchers.index', compact('dispatchers', 'q'));
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
