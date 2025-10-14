<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\DoctorSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use PhpParser\Comment\Doc;

class DoctorScheduleController extends Controller
{
    public function index(Request $request)
    {
        $docId = Auth::id();

        // Load existing availability keyed by weekday
        $rows = DoctorSchedule::where('doctor_id', $docId)->get()->keyBy('weekday');

        // Map to form-friendly array: ['Mon'=>['start'=>'09:00','end'=>'17:00','enabled'=>true], ...]
        $weeknames = DoctorSchedule::weekdays();
        $schedule = [];
        foreach ($weeknames as $w => $name) {
            $row = $rows->get($w);
            $schedule[$name] = [
                'start'   => $row?->start_time ?? '09:00',
                'end'     => $row?->end_time   ?? '17:00',
                'enabled' => $row?->enabled    ?? true,
            ];
        }

        // Upcoming appointments (next 14 days)
        $upcoming = Appointment::with(['patient:id,first_name,last_name'])
            ->where('doctor_id', $docId)
            ->where('status', 'scheduled')
            ->where('scheduled_at', '>=', now())
            ->where('scheduled_at', '<', now()->copy()->addDays(14))
            ->orderBy('scheduled_at')
            ->take(12)
            ->get();

        return view('doctor.schedule', compact('schedule', 'upcoming'));
    }

    public function store(Request $request)
    {
        $docId     = Auth::id();
        $weeknames = DoctorSchedule::weekdays();          // [1=>'Mon', 2=>'Tue', ... 7=>'Sun']
        $labels    = array_values($weeknames);            // ['Mon','Tue',...,'Sun']
        $labelSet  = array_flip($labels);                 // fast lookup

        // Pull arrays and coerce types
        $start   = (array) $request->input('start', []);
        $end     = (array) $request->input('end', []);
        $enabled = (array) $request->input('enabled', []); // checkboxes: only present when checked

        // 1) Basic shape validation and allowed keys check
        $v = Validator::make($request->all(), [
            'start'   => ['required', 'array'],
            'end'     => ['required', 'array'],
            'enabled' => ['sometimes', 'array'],
        ]);

        // Reject unknown keys to prevent tampering
        $unknownStart   = array_diff(array_keys($start),   $labels);
        $unknownEnd     = array_diff(array_keys($end),     $labels);
        $unknownEnabled = array_diff(array_keys($enabled), $labels);

        if (!empty($unknownStart) || !empty($unknownEnd) || !empty($unknownEnabled)) {
            $v->after(function ($v) use ($unknownStart, $unknownEnd, $unknownEnabled) {
                if ($unknownStart)   $v->errors()->add('start',   'Invalid weekday keys: ' . implode(', ', $unknownStart));
                if ($unknownEnd)     $v->errors()->add('end',     'Invalid weekday keys: ' . implode(', ', $unknownEnd));
                if ($unknownEnabled) $v->errors()->add('enabled', 'Invalid weekday keys: ' . implode(', ', $unknownEnabled));
            });
        }

        // 2) Per-day rules when enabled: require valid times, end > start
        $v->after(function ($v) use ($labels, $start, $end, $enabled) {
            foreach ($labels as $label) {
                $isEnabled = array_key_exists($label, $enabled);

                // If not enabled, skip strict time validation
                if (!$isEnabled) {
                    continue;
                }

                $s = $start[$label] ?? null;
                $e = $end[$label]   ?? null;

                if (!$s) {
                    $v->errors()->add("start.$label", "Start time is required for $label.");
                    continue;
                }
                if (!$e) {
                    $v->errors()->add("end.$label", "End time is required for $label.");
                    continue;
                }

                // Format check
                try {
                    $cs = Carbon::createFromFormat('H:i', $s);
                } catch (\Exception $ex) {
                    $v->errors()->add("start.$label", "Start time must be HH:MM.");
                    $cs = null;
                }

                try {
                    $ce = Carbon::createFromFormat('H:i', $e);
                } catch (\Exception $ex) {
                    $v->errors()->add("end.$label", "End time must be HH:MM.");
                    $ce = null;
                }

                if ($cs && $ce && $ce->lessThanOrEqualTo($cs)) {
                    $v->errors()->add("end.$label", "End time must be after start time for $label.");
                }
            }
        });

        if ($v->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Please fix the highlighted errors.',
                'errors'  => $v->errors(),
            ], 422);
        }

        // 3) Persist: loop Mon..Sun by weekday number key
        foreach ($weeknames as $weekday => $label) {
            $isEnabled = array_key_exists($label, $enabled);

            // For disabled days: store null times + enabled=false
            $sVal = $isEnabled ? ($start[$label] ?? null) : null;
            $eVal = $isEnabled ? ($end[$label]   ?? null) : null;

            DoctorSchedule::updateOrCreate(
                ['doctor_id' => $docId, 'weekday' => $weekday],
                [
                    'start_time' => $sVal,
                    'end_time'   => $eVal,
                    'enabled'    => $isEnabled,
                ]
            );
        }

        return response()->json(['status' => 'success', 'message' => 'Schedule saved']);
    }
}
