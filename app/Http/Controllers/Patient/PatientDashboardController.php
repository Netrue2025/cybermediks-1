<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Prescription;
use App\Models\Product;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PatientDashboardController extends Controller
{
    public function index(Request $r)
    {
        $userId = $r->user()->id;

        // Next accepted appointment in the future (video/chat/in_person)
        $pendingAppointments = Appointment::where('patient_id', $userId)
            ->whereIn('status', ['pending'])
            ->count();

        $acceptedAppt = Appointment::with('doctor')
            ->where('patient_id', $userId)
            ->where('status', 'accepted')
            ->whereNotNull('meeting_link')
            ->orderByDesc('updated_at')
            ->first();

        $timer = $acceptedAppt->doctor->doctorProfile->avg_duration ?? 15;

        $endEpoch = null;

        // DB “now” in UTC (fallback to PHP time() if DB call fails)
        $row = DB::selectOne("SELECT UNIX_TIMESTAMP(UTC_TIMESTAMP()) AS ts");
        $nowEpoch = $row ? (int) $row->ts : time();

        if ($acceptedAppt) {
            $updatedTs = optional($acceptedAppt->updated_at)?->getTimestamp();
            if ($updatedTs) {
                $endEpoch = $updatedTs + ((int) $timer * 60);
            }
        }

        $remaining = $endEpoch ? max(0, $endEpoch - $nowEpoch) : 0;

        // dd($timer);
        // Active prescriptions
        $activeRxCount = Prescription::where('patient_id', $userId)
            ->where('status', 'active')->count();

        // Specialties for chips/filter
        $specialties = Specialty::orderBy('name')->get(['id', 'name', 'slug', 'icon', 'color']);

        // Nearby pharmacies count (if we have user lat/lng in session or on users table)
        $nearbyCount = 0;
        $lat = $r->user()->lat ?? session('lat');
        $lng = $r->user()->lng ?? session('lng');

        if ($lat && $lng) {
            // Within 10km radius using Haversine on users (role=pharmacist)
            $nearbyCount = User::pharmacists()
                ->whereNotNull('lat')->whereNotNull('lng')
                ->whereRaw("
            (6371 * acos(
                cos(radians(?)) * cos(radians(lat)) *
                cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat))
            )) <= 10
        ", [$lat, $lng, $lat])
                ->count();
        }

        $prescriptions = Prescription::query()
            ->with([
                'doctor:id,first_name,last_name',
                'items:id,prescription_id,drug,dose,frequency,days,quantity,directions',
                'order:id,prescription_id,patient_id,pharmacy_id,status,items_subtotal,dispatcher_price,grand_total',
                'order.items:id,order_id,prescription_item_id,status,unit_price,line_total',
            ])
            ->forPatient($userId)
            ->where(function ($q) {
                $q->whereHas('order', fn($oq) => $oq->whereNotIn('status', ['delivered']))
                    ->orDoesntHave('order');
            })
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('patient.dashboard', [
            'pendingAppointments'   => $pendingAppointments,
            'activeRxCount'  => $activeRxCount,
            'nearbyCount'    => $nearbyCount,
            'specialties'    => $specialties,
            'acceptedAppt'   => $acceptedAppt,
            'meetingTimer'   => $timer,
            'prescriptions'  => $prescriptions,
            'acceptedAppt'   => $acceptedAppt,
            'meetingTimer'   => $timer,
            'meet_end_epoch' => $endEpoch,
            'meet_now_epoch' => $nowEpoch,
            'meet_remaining' => $remaining
        ]);
    }

    public function products(Request $r)
    {
        $q = trim((string) $r->query('q', ''));

        $products = Product::query()
            ->when($q !== '', function ($w) use ($q) {
                $w->where(function ($x) use ($q) {
                    $x->where('name', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('updated_at')
            ->paginate(24)                 // paginate for nicer UX
            ->withQueryString();           // keep ?q= in pagination links

        return view('patient.store', compact('products', 'q'));
    }
}
