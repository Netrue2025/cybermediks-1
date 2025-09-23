<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;

class AdminAppointmentsController extends Controller
{
    public function index(Request $r)
    {
        $status = $r->query('status');
        $date   = $r->query('date');
        $q      = trim((string)$r->query('q'));

        $appts = Appointment::with(['doctor', 'patient'])
            ->when($status, fn($w) => $w->where('status', $status))
            ->when($date, fn($w) => $w->whereDate('scheduled_at', $date))
            ->when($q !== '', function ($w) use ($q) {
                $w->where(function ($x) use ($q) {
                    $x->where('title', 'like', "%$q%")
                        ->orWhereHas('patient', fn($p) => $p->where('first_name', 'like', "%$q%")->orWhere('last_name', 'like', "%$q%"))
                        ->orWhereHas('doctor', fn($d) => $d->where('first_name', 'like', "%$q%")->orWhere('last_name', 'like', "%$q%"));
                });
            })
            ->orderByDesc('scheduled_at')
            ->paginate(20);

        return view('admin.appointments.index', compact('appts', 'status', 'date', 'q'));
    }
}
