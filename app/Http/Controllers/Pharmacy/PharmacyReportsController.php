<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\Prescription;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PharmacyReportsController extends Controller
{
    public function index(Request $r)
    {
        $from = $r->query('from') ?: now()->toDateString();
        $to   = $r->query('to')   ?: now()->toDateString();

        // Revenue: either from transactions tagged as pharmacy sales,
        // or from prescriptions total_amount where status picked.
        $revenue = WalletTransaction::where('user_id', Auth::id())
            ->whereBetween(DB::raw('date(created_at)'), [$from, $to])
            ->where('purpose', 'pharmacy_sale')
            ->sum('amount');

        // KPIs from prescriptions handled by this pharmacy (if you track pharmacy_id on prescriptions)
        $base = Prescription::whereBetween(DB::raw('date(created_at)'), [$from, $to]);
        if (\Illuminate\Support\Facades\Schema::hasColumn('prescriptions', 'pharmacy_id')) {
            $base->where('pharmacy_id', Auth::id());
        }

        $countPending  = (clone $base)->where('dispense_status', 'pending')->count();
        $countReady    = (clone $base)->where('dispense_status', 'ready')->count();
        $countPicked   = (clone $base)->where('dispense_status', 'picked')->count();
        $countCanceled = (clone $base)->where('dispense_status', 'cancelled')->count();
        $filled        = $countReady + $countPicked;

        // Top medicines (by occurrences)
        $topMeds = DB::table('prescription_items')
            ->join('prescriptions', 'prescription_items.prescription_id', '=', 'prescriptions.id')
            ->when(\Illuminate\Support\Facades\Schema::hasColumn('prescriptions', 'pharmacy_id'), fn($q) => $q->where('pharmacy_id', Auth::id()))
            ->whereBetween(DB::raw('date(prescriptions.created_at)'), [$from, $to])
            ->selectRaw('COALESCE(prescription_items.drug, prescription_items.drug) as name, COUNT(*) as cnt')
            ->groupBy('name')
            ->orderByDesc('cnt')->limit(10)->get();

        // Low stock snapshot
        $lowStock = InventoryItem::where('pharmacy_id', Auth::id())
            ->whereColumn('qty', '<=', 'reorder_level')
            ->orderBy('name')->limit(10)->get();

        return view('pharmacy.reports.index', compact(
            'from',
            'to',
            'revenue',
            'countPending',
            'countReady',
            'countPicked',
            'countCanceled',
            'filled',
            'topMeds',
            'lowStock'
        ));
    }
}
