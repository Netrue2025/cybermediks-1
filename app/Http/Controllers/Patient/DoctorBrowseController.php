<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class DoctorBrowseController extends Controller
{
    public function index(Request $r)
    {
        $search       = trim((string)$r->query('q'));
        $specialtyId  = $r->integer('specialty_id');
        $available    = (bool)$r->boolean('available');

        $q = User::query()
            ->where('role', 'doctor')
            ->with([
                'doctorProfile:id,doctor_id,is_available',
                'specialties:id,name'
            ])
            ->withCasts(['doctor_profile.is_available' => 'boolean']);

        if ($available) {
            $q->whereHas('doctorProfile', fn($p) => $p->where('is_available', true));
        }

        if ($specialtyId) {
            $q->whereHas('specialties', fn($s) => $s->where('specialty_id', $specialtyId));
        }

        if ($search !== '') {
            $q->where(function ($w) use ($search) {
                $w->where('name', 'like', "%{$search}%")
                    ->orWhereHas('doctorProfile', fn($p) => $p->where('title', 'like', "%{$search}%"));
            });
        }

        $doctors = $q->orderBy('first_name')->paginate(10);

        return response()->json([
            'data' => $doctors->map(function ($d) {
                $initials = strtoupper(substr($d->first_name, 0, 1)) . strtoupper(substr($d->last_name, 0, 1));
                return [
                    'id'          => $d->id,
                    'first_name'  => $d->first_name,
                    'last_name'   => $d->last_name,
                    'initials'    => strtoupper($initials),
                    'available'   => (bool)optional($d->doctorProfile)->is_available,
                    'specialties' => $d->specialties->pluck('name')->all(),
                ];
            }),
            'next_page_url' => $doctors->nextPageUrl(),
        ]);
    }
}
