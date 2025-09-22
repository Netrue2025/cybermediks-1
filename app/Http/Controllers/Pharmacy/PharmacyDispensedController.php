<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PharmacyDispensedController extends Controller
{
    public function index(Request $r)
    {
        $q    = trim((string)$r->query('q',''));
        $from = $r->query('from') ?: now()->toDateString();
        $to   = $r->query('to')   ?: now()->toDateString();

        $rx = Prescription::query()
            ->with([
                'patient:id,first_name,last_name,email',
                'doctor:id,first_name,last_name',
                'items:id,prescription_id,drug,quantity'
            ])
            ->where('dispense_status','picked')
            // if you track which pharmacy filled it, keep this; otherwise remove
            ->when(Schema::hasColumn('prescriptions','pharmacy_id'),
                fn($w)=>$w->where('pharmacy_id', Auth::id()))
            ->whereBetween(DB::raw('date(updated_at)'), [$from,$to]);

        if ($q !== '') {
            $rx->where(function($w) use ($q){
                $w->where('code','like',"%{$q}%")
                  ->orWhereHas('patient', fn($p)=>$p->where('first_name','like',"%{$q}%")
                                                    ->orWhere('last_name','like',"%{$q}%")
                                                    ->orWhere('email','like',"%{$q}%"))
                  ->orWhereHas('items', fn($i)=>$i->where('drug','like',"%{$q}%"));
            });
        }

        $prescriptions = $rx->orderByDesc('updated_at')->paginate(12)->withQueryString();

        if ($r->ajax()) {
            return view('pharmacy.dispensed._list', compact('prescriptions'))->render();
        }

        // simple totals
        $totalAmount = (clone $rx)->sum('total_amount');
        $count = (clone $rx)->count();

        return view('pharmacy.dispensed.index', compact('prescriptions','q','from','to','totalAmount','count'));
    }

    public function undo(Prescription $rx)
    {
        // guard ownership if pharmacy_id exists
        if (Schema::hasColumn('prescriptions','pharmacy_id')) {
            abort_unless($rx->pharmacy_id === Auth::id(), 403);
        }
        abort_unless($rx->dispense_status === 'picked', 422, 'Only picked prescriptions can be reverted.');
        $rx->update(['dispense_status' => 'ready']);
        return response()->json(['status'=>'ok','message'=>'Order reverted to Ready.']);
    }

    public function receipt(Prescription $rx)
    {
        if (Schema::hasColumn('prescriptions','pharmacy_id')) {
            abort_unless($rx->pharmacy_id === Auth::id(), 403);
        }
        $rx->loadMissing('patient','doctor','items');
        return view('pharmacy.dispensed.receipt', compact('rx'));
    }
}
