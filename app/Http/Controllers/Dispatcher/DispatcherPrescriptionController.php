<?php

namespace App\Http\Controllers\Dispatcher;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DispatcherPrescriptionController extends Controller
{
    public function accept(Request $r, Prescription $rx)
    {
        // Only READY and unassigned can be accepted
        if ($rx->dispense_status !== 'ready') {
            return response()->json(['message' => 'Only ready prescriptions can be accepted'], 422);
        }
        if (!is_null($rx->dispatcher_id)) {
            return response()->json(['message' => 'Already assigned'], 422);
        }

        $rx->dispatcher_id = Auth::id();
        $rx->save();

        return response()->json(['status' => 'ok', 'message' => 'Delivery accepted']);
    }
}
