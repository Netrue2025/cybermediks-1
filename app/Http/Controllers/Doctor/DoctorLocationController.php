<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DoctorLocationController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'lat' => ['required','numeric','between:-90,90'],
            'lng' => ['required','numeric','between:-180,180'],
            'accuracy' => ['nullable','numeric','min:0'],
        ]);

        $user = $request->user();

        // If you added columns on users table:
        if ($user->isFillable('lat') && $user->isFillable('lng')) {
            $user->forceFill([
                'lat' => $data['lat'],
                'lng' => $data['lng'],
                'loc_accuracy' => $data['accuracy'] ?? null,
            ])->save();
        } else {
            // Otherwise, keep it in session (temporary) or a separate table
            session([
                'geo.lat' => $data['lat'],
                'geo.lng' => $data['lng'],
                'geo.accuracy' => $data['accuracy'] ?? null,
            ]);
        }

        return response()->json(['ok'=>true,'message'=>'Location saved']);
    }
}
