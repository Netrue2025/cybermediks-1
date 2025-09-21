<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\DoctorSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        return view('doctor.schedule', compact('schedule','upcoming'));
    }

    public function store(Request $request)
    {
        $docId = Auth::id();
        $weeknames = DoctorSchedule::weekdays(); // 1..7 => Mon..Sun

        // Validate incoming arrays: start[Mon], end[Mon], enabled[Mon]
        $data = $request->validate([
            'start'   => ['required','array'],
            'end'     => ['required','array'],
            'enabled' => ['array'], // checkbox only sends for checked
        ]);

        // Normalize & save per day
        foreach ($weeknames as $weekday => $label) {
            $start = $data['start'][$label] ?? null;
            $end   = $data['end'][$label] ?? null;
            $en    = array_key_exists($label, ($data['enabled'] ?? [])); // checked => key exists

            // Basic per-day validation
            if ($en) {
                // both must be valid HH:MM and end > start
                $request->validate([
                    "start.$label" => ['required','date_format:H:i'],
                    "end.$label"   => ['required','date_format:H:i','after:start.'.$label],
                ]);
            }

            DoctorSchedule::updateOrCreate(
                ['doctor_id' => $docId, 'weekday' => $weekday],
                [
                    'start_time' => $en ? $start : null,
                    'end_time'   => $en ? $end   : null,
                    'enabled'    => $en,
                ]
            );
        }

        return response()->json(['status'=>'success','message'=>'Schedule saved']);
    }
}
