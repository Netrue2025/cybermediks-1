<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DoctorBrowseController extends Controller
{
    public function index(Request $r)
    {
        $search       = trim((string)$r->query('q'));
        $specialtyId  = $r->integer('specialty_id');
        $available    = (bool)$r->boolean('available');
        $dateFilter   = $r->query('date'); // optional YYYY-MM-DD for patients to pick a day

        // base query
        $q = User::query()
            ->where('role', 'doctor')
            ->with([
                'doctorProfile:id,doctor_id,is_available,title,consult_fee,avg_duration',
                'specialties:id,name',
            ])
            ->withCasts(['doctor_profile.is_available' => 'boolean']);

        // Only doctors marked available (profile switch)
        if ($available) {
            $q->whereHas('doctorProfile', fn($p) => $p->where('is_available', true))
                // …and with at least one enabled schedule window
                ->whereHas('schedules', fn($s) => $s->where('enabled', true));
        }

        // Filter by specialty
        if ($specialtyId) {
            $q->whereHas('specialties', fn($s) => $s->where('specialty_id', $specialtyId));
        }

        // Search by name or title
        if ($search !== '') {
            $q->where(function ($w) use ($search) {
                $w->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhereHas('doctorProfile', fn($p) => $p->where('title', 'like', "%{$search}%"));
            });
        }

        // If the patient picked a date, only show doctors who have an enabled window on that day
        if ($dateFilter) {
            $day = Carbon::parse($dateFilter)->dayOfWeek; // 0=Sun
            $q->whereHas('schedules', function ($s) use ($day) {
                $s->where('enabled', true)->where('weekday', $day);
            });
        }

        // Eager-load schedules minimally for next-slot calculation (no N+1)
        $q->with(['schedules' => function ($s) {
            $s->select('id', 'doctor_id', 'weekday', 'start_time', 'end_time', 'enabled')
                ->where('enabled', true);
        }]);

        $doctors = $q->orderBy('first_name')->paginate(10);

        // Helper to compute the next slot within next N days
        $computeNextSlot = function ($doctor) use ($dateFilter) {
            // base = either selected date’s midnight or now
            $base = $dateFilter ? Carbon::parse($dateFilter)->startOfDay() : Carbon::now();
            $limit = (clone $base)->addDays(14); // look ahead two weeks

            // Build a map: dayOfWeek => array of windows
            $byDay = [];
            foreach ($doctor->schedules as $sch) {
                $byDay[$sch->weekday][] = [$sch->start_time, $sch->end_time];
            }

            $cursor = $base->copy();
            while ($cursor->lte($limit)) {
                $dow = $cursor->dayOfWeek; // 0..6
                if (!empty($byDay[$dow])) {
                    foreach ($byDay[$dow] as [$start, $end]) {
                        // form a concrete start datetime on this day
                        $startAt = Carbon::parse($cursor->toDateString() . ' ' . $start);
                        // if we're computing for today and the start already passed, skip
                        if ($startAt->lt(Carbon::now()) && !$dateFilter) continue;

                        return $startAt; // first valid window start
                    }
                }
                $cursor->addDay()->startOfDay();
            }
            return null;
        };

        return response()->json([
            'data' => $doctors->map(function ($d) use ($computeNextSlot) {
                $initials = strtoupper(substr($d->first_name, 0, 1)) . strtoupper(substr($d->last_name, 0, 1));
                $next = $computeNextSlot($d);
                return [
                    'id'            => $d->id,
                    'first_name'    => $d->first_name,
                    'last_name'     => $d->last_name,
                    'initials'      => $initials,
                    'charges'       => $d->doctorProfile?->consult_fee ?? 0,
                    'duration'      => $d->doctorProfile?->avg_duration ?? 15,
                    'title'         => optional($d->doctorProfile)->title,
                    'available'     => (bool)optional($d->doctorProfile)->is_available,
                    'specialties'   => $d->specialties->pluck('name')->all(),
                    'next_slot_iso' => $next?->toIso8601String(),
                    'next_slot_human' => $next?->format('D, M j · g:ia'),
                    'has_availability' => (bool)$next,
                    'appointment_url'  => route('patient.appointments.create', ['doctor_id' => $d->id, 'type' => 'video']),
                    'chat_url'         => route('patient.messages', ['doctor_id' => $d->id]),
                ];
            }),
            'next_page_url' => $doctors->nextPageUrl(),
        ]);
    }

    public function show(User $doctor)
    {
        abort_unless($doctor->role === 'doctor', 404);

        $doctor->load([
            'doctorProfile:id,doctor_id,title,bio,is_available,consult_fee,avg_duration',
            'specialties:id,name',
            'schedules:id,doctor_id,weekday,start_time,end_time,enabled',
        ]);

        // Compute next up to 5 slots within 14 days (like we did in list)
        $nextSlots = $this->nextSlots($doctor, 5);

        return response()->json([
            'id'           => $doctor->id,
            'first_name'   => $doctor->first_name,
            'last_name'    => $doctor->last_name,
            'title'        => $doctor->doctorProfile?->title,
            'bio'          => $doctor->doctorProfile?->bio,
            'available'    => (bool) $doctor->doctorProfile?->is_available,
            'consult_fee'  => $doctor->doctorProfile?->consult_fee,
            'avg_duration' => $doctor->doctorProfile?->avg_duration,
            'specialties'  => $doctor->specialties->pluck('name')->values(),
            'next_slots'   => collect($nextSlots)->map(fn($dt)=>[
                'iso'   => $dt->toIso8601String(),
                'human' => $dt->format('D, M j · g:ia'),
            ])->values(),
            // useful links
            'appointment_url' => route('patient.appointments.create', ['doctor_id' => $doctor->id, 'type'=>'video']),
            'chat_url'        => route('patient.messages', ['doctor_id' => $doctor->id]),
        ]);
    }

    /** Return up to $count Carbon start datetimes within 14 days. */
    protected function nextSlots(User $doctor, int $count = 5): array
    {
        $windowsByDay = [];
        foreach ($doctor->schedules as $s) {
            if (!$s->enabled) continue;
            $windowsByDay[$s->weekday][] = [$s->start_time, $s->end_time];
        }

        $results = [];
        $cursor = Carbon::now()->startOfDay();
        $limit  = (clone $cursor)->addDays(14);

        while ($cursor->lte($limit) && count($results) < $count) {
            $dow = $cursor->dayOfWeek; // 0..6
            if (!empty($windowsByDay[$dow])) {
                foreach ($windowsByDay[$dow] as [$start, $end]) {
                    $startAt = Carbon::parse($cursor->toDateString().' '.$start);
                    if ($startAt->lt(Carbon::now())) continue; // skip past
                    $results[] = $startAt;
                    if (count($results) >= $count) break 2;
                }
            }
            $cursor->addDay()->startOfDay();
        }

        return $results;
    }
}
