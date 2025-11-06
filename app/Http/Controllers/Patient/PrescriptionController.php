<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Prescription;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\AIQuoteService;
use App\Services\InventoryReader;
use App\Services\OrderFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PrescriptionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $status = match (strtolower((string) $request->get('status'))) {
            'active' => 'active',
            'expired' => 'expired',
            'refill requested', 'refill_requested' => 'refill_requested',
            default => null
        };
        $prescriptions = Prescription::query()
            ->with([
                'doctor:id,first_name,last_name',
                'items:id,prescription_id,drug,dose,frequency,days,quantity,directions',
                'order:id,prescription_id,patient_id,pharmacy_id,status,items_subtotal,dispatcher_price,grand_total',
                'order.items:id,order_id,prescription_item_id,status,unit_price,line_total'
            ])
            ->forPatient($user->id)
            ->when($request->from === 'dashboard', function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('order', fn($oq) => $oq->whereNotIn('status', ['delivered']))
                        ->orDoesntHave('order');
                });
            })
            ->statusIs($status)
            ->search($request->get('q'))
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $acceptedAppt = Appointment::with('doctor')
            ->where('patient_id', $user->id)
            ->where('status', 'accepted')
            ->whereNotNull('meeting_link')
            ->orderByDesc('updated_at')
            ->first();

        $timer = $acceptedAppt->doctor->doctorProfile->avg_duration ?? 15;

        $meet_end_epoch = null;

        // DB “now” in UTC (fallback to PHP time() if DB call fails)
        $row = DB::selectOne("SELECT UNIX_TIMESTAMP(UTC_TIMESTAMP()) AS ts");
        $meet_now_epoch = $row ? (int) $row->ts : time();

        if ($acceptedAppt) {
            $updatedTs = optional($acceptedAppt->updated_at)?->getTimestamp();
            if ($updatedTs) {
                $meet_end_epoch = $updatedTs + ((int) $timer * 60);
            }
        }

        $meet_remaining = $meet_end_epoch ? max(0, $meet_end_epoch - $meet_now_epoch) : 0;

        if ($request->ajax()) {
            $html = view('patient.prescriptions._list', compact('prescriptions', 'acceptedAppt', 'meet_remaining', 'meet_end_epoch', 'meet_now_epoch'))->render();
            $etag = md5($html);

            $response = response($html, 200)
                ->header('Content-Type', 'text/html; charset=UTF-8')
                ->setEtag($etag);

            if ($response->isNotModified($request)) {
                return $response; // 304
            }

            return $response;
        }

        return view('patient.prescriptions.index', compact('prescriptions'));
    }

    public function list(Request $r, Prescription $rx)
    {
        abort_unless($rx->patient_id === Auth::id(), 403);

        $q = trim((string)$r->query('q', ''));
        $filter = $r->query('filter'); // '', '24_7', 'delivery'

        $pharmacies = User::query()
            ->where('role', 'pharmacy')
            ->with(['pharmacyProfile' => function ($p) {
                $p->select('id', 'user_id', 'hours', 'is_24_7', 'delivery_radius_km');
            }])
            ->when($q !== '', function ($w) use ($q) {
                $w->where(function ($x) use ($q) {
                    $x->where('first_name', 'like', "%{$q}%")
                        ->orWhere('last_name', 'like', "%{$q}%")
                        ->orWhere('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->when($filter === '24_7', fn($w) => $w->whereHas('pharmacyProfile', fn($p) => $p->where('is_24_7', true)))
            // ->when($filter === 'delivery', fn($w)=>$w->whereHas('pharmacyProfile', fn($p)=>$p->whereNotNull('delivery_radius_km')))
            ->orderBy('first_name')
            ->take(50)
            ->get();

        return view('patient.prescriptions._pharmacy_list', compact('pharmacies'))->render();
    }

    public function assign(Request $request, Prescription $rx)
    {
        if ($rx->patient_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'pharmacy_id' => 'required|exists:users,id',
        ]);

        // Create or reuse order, mirror items if creating the first time
        $order = OrderFactory::ensureOrderFromPrescription($rx, (int)$data['pharmacy_id']);

        // Keep legacy fields in sync
        $rx->forceFill([
            'pharmacy_id'      => $data['pharmacy_id'],
            'dispense_status'  => 'pending',
            'total_amount'     => null,
            'dispatcher_id'    => null,
            'dispatcher_price' => null,
        ])->save();

        // Try to immediately quote using the pharmacy CSV
        $quoteSummary = null;
        $profile = $rx->pharmacy?->pharmacyProfile;
        $path    = $profile?->inventory_path;

        if ($path) {
            $quoteSummary = $this->performQuote($rx, $order, $path);
        } else {
            // No CSV: leave order pending
            $quoteSummary = [
                'ok'          => false,
                'status'      => $order->status,
                'items_total' => 0,
                'available'   => [],
                'unavailable' => [],
                'message'     => 'Pharmacy has no inventory file. Please try another pharmacy.',
            ];
        }

        return response()->json([
            'ok'       => true,
            'message'  => $quoteSummary['message'] ?? 'Pharmacy selected.',
            'order_id' => $order->id,
            'quote'    => $quoteSummary, // UI can use this to show available/unavailable + totals
        ]);
    }

    public function quote(Request $r, Prescription $rx)
    {
        abort_unless($rx->patient_id === Auth::id(), 403);

        $pharmacyId = $rx->pharmacy_id;
        if (!$pharmacyId || !$rx->pharmacy?->pharmacyProfile?->inventory_path) {
            return response()->json(['message' => 'No pharmacy inventory available.'], 422);
        }

        $order   = OrderFactory::ensureOrderFromPrescription($rx, $pharmacyId);
        $summary = $this->performQuote($rx, $order, $rx->pharmacy->pharmacyProfile->inventory_path);

        // Same shape as before
        return response()->json($summary);
    }

    /**
     * Centralized quoting logic used by both assign() and quote().
     *
     * @return array { ok, status, items_total, available[], unavailable[], message }
     */
    private function performQuote(Prescription $rx, $order, string $inventoryPath): array
    {
        // Reload with relations we need
        $order->load(['items', 'prescription.items']);

        $csvText = InventoryReader::readCsvText($inventoryPath);
        if (!$csvText) {
            return [
                'ok'          => false,
                'status'      => $order->status,
                'items_total' => 0,
                'available'   => [],
                'unavailable' => [],
                'message'     => 'Inventory file missing.',
            ];
        }

        // Build Rx items for AI
        $rxItems = $order->prescription->items->map(fn($i) => [
            'drug'      => $i->drug,
            'dose'      => $i->dose,
            'frequency' => $i->frequency,
            'days'      => $i->days,
            'quantity'  => $i->quantity ?: 1,
        ])->values()->all();

        // Ask AI to match + price
        $ai = AIQuoteService::quote($rxItems, $csvText);

        // Index order_items by normalized drug name
        $byName = $order->items->keyBy(fn($it) => strtolower(trim($it->drug)));

        $subtotal = 0;
        DB::transaction(function () use ($ai, $byName, &$subtotal, $order) {
            // Reset any previous quotes on this order
            $order->items()->update(['unit_price' => null, 'line_total' => null, 'status' => 'pending']);

            foreach (($ai['available'] ?? []) as $row) {
                $name = strtolower(trim((string)($row['drug'] ?? '')));
                $unit = (float) ($row['unit_price'] ?? 0);
                $line = (float) ($row['line_total'] ?? 0);
                if (!$name || $unit <= 0) continue;

                if ($it = $byName->get($name)) {
                    $qty = max(1, (int)($it->quantity ?: 1));
                    $it->update([
                        'unit_price' => $unit,
                        'line_total' => $line > 0 ? $line : ($unit * $qty),
                        'status'     => 'quoted',
                    ]);
                    $subtotal += (float)$it->line_total;
                }
            }

            $order->update([
                'items_subtotal' => $subtotal ?: null,
                'status'         => $subtotal > 0 ? 'quoted' : 'pending',
            ]);

            // Optional legacy sync for your current UI:
            if ($subtotal > 0) {
                $order->prescription->forceFill([
                    'dispense_status' => 'price_assigned',
                    'total_amount'    => $subtotal,
                ])->save();
            }
        });

        // ---- Delivery fee calculation (₦100 per km) ----
        // Adjust attribute names to your schema.
        $patientLat  = (float) ($rx->patient->lat   ?? $rx->patient->latitude   ?? 0);
        $patientLng  = (float) ($rx->patient->lng   ?? $rx->patient->longitude  ?? 0);
        $pharmacyLat = (float) ($rx->pharmacy->lat  ?? $rx->pharmacy->latitude  ?? 0);
        $pharmacyLng = (float) ($rx->pharmacy->lng  ?? $rx->pharmacy->longitude ?? 0);

        $distanceKm = 0.0;
        if ($patientLat && $patientLng && $pharmacyLat && $pharmacyLng) {
            $distanceKm = $this->distanceKm($patientLat, $patientLng, $pharmacyLat, $pharmacyLng);
        }

        $ratePerKm    = 100.0; // change to your currency/rate
        $deliveryFee  = $distanceKm > 0 ? round($distanceKm * $ratePerKm, 2) : 0.0;

        // Persist on the order so the rest of the flow sees it (you already use dispatcher_price as fee)
        $order->forceFill([
            'dispatcher_price' => $deliveryFee,
        ])->save();

        $grandTotal = round((float)$subtotal + (float)$deliveryFee, 2);

        return [
            'ok'          => true,
            'status'      => $order->fresh()->status,
            'items_total' => $subtotal ?: 0,
            'delivery_fee' => $deliveryFee,
            'distance_km'  => round($distanceKm, 2),
            'grand_total'  => $grandTotal,
            'available'   => $ai['available'] ?? [],
            'unavailable' => $ai['unavailable'] ?? [],
            'message'     => $subtotal > 0
                ? 'Prices found. Please review and confirm.'
                : 'No matching items found in this pharmacy inventory.',
        ];
    }

    private function distanceKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        // Haversine formula
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }



    public function confirm(Request $r, Prescription $rx)
    {
        abort_unless($rx->patient_id === Auth::id(), 403);

        $order = $rx->order()->with('items')->first();
        if (!$order) {
            return response()->json(['message' => 'No order to confirm yet'], 422);
        }

        if ($order->status !== 'quoted') {
            // For legacy front-end: still allow old flow if price_assigned is used
            if (($rx->dispense_status ?? 'pending') === 'price_assigned' && !is_null($rx->total_amount)) {
                // mark legacy state as confirmed, but also move order forward minimally
                $rx->update(['dispense_status' => 'price_confirmed']);
                // Move any quoted items to patient_confirmed
                DB::transaction(function () use ($order) {
                    $order->items()->where('status', 'quoted')->update(['status' => 'patient_confirmed']);
                    if (! $order->items()->where('status', 'quoted')->exists()) {
                        $order->update(['status' => 'patient_confirmed']);
                    }
                });
                return response()->json(['status' => 'ok', 'message' => 'Price confirmed (legacy).']);
            }

            return response()->json(['message' => 'Not ready for confirmation'], 422);
        }

        DB::transaction(function () use ($order, $rx) {
            $order->items()->where('status', 'quoted')->update(['status' => 'patient_confirmed']);
            $order->update(['status' => 'patient_confirmed']);

            // Optional legacy sync:
            $rx->forceFill([
                'dispense_status' => 'price_confirmed',
                'total_amount'    => $order->items_subtotal, // if you want to echo subtotal
            ])->save();
        });

        return response()->json(['status' => 'ok', 'message' => 'Items confirmed']);
    }

    public function confirmDeliveryFee(Request $r, Order $order)
    {
        // Ensure this order belongs to the current patient
        $patientId = $order->patient_id ?? $order->prescription?->patient_id;
        abort_unless($patientId === Auth::id(), 403);

        if ($order->status !== 'dispatcher_price_set') {
            return response()->json(['message' => 'No dispatcher fee pending confirmation'], 422);
        }

        if (is_null($order->dispatcher_price)) {
            return response()->json(['message' => 'Dispatcher price missing'], 422);
        }

        // Move order forward and compute grand total
        $order->status      = 'dispatcher_price_confirm';
        $order->grand_total = (float)($order->items_subtotal ?? 0) + (float)$order->dispatcher_price;
        $order->save();

        // (Optional) legacy sync to prescription
        if ($order->relationLoaded('prescription') || $order->prescription) {
            $order->prescription->forceFill([
                'dispense_status'   => 'dispatcher_price_confirm',
                'dispatcher_price'  => $order->dispatcher_price,
            ])->save();
        }

        return response()->json(['status' => 'ok', 'message' => 'Delivery fee confirmed']);
    }

    public function quoteFragment(Order $order)
    {
        $order->load([
            'items:id,order_id,drug,quantity,unit_price,line_total,status',
            'prescription.patient:id',
            'pharmacy',
        ]);

        // Available = items that got quoted
        $available = $order->items
            ->where('status', 'quoted')
            ->map(function ($it) {
                $qty  = max(1, (int)($it->quantity ?? 1));
                $unit = (float)($it->unit_price ?? 0);
                $line = (float)($it->line_total ?: $unit * $qty);
                return [
                    'drug'       => $it->drug,
                    'unit_price' => $unit,
                    'line_total' => $line,
                ];
            })
            ->values();

        // We can’t reconstruct “unavailable” reliably without the AI payload; leave empty
        $unavailable = collect([]);

        $items_total  = (float)($order->items_subtotal ?? $available->sum('line_total'));
        $delivery_fee = (float)($order->dispatcher_price ?? 0);

        // If you persisted distance somewhere, use it; else 0
        $distance_km  = (float)($order->distance_km ?? 0.0);

        return view('patient.prescriptions._quote', [
            'order'        => $order,
            'available'    => $available,
            'unavailable'  => $unavailable,
            'items_total'  => $items_total,
            'delivery_fee' => $delivery_fee,
            'distance_km'  => $distance_km,
        ]);
    }
}
