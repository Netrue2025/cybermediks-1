<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PatientLocationController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'min:0'],
        ]);

        $user = $request->user();
        $user->update([
            'lat' => $data['lat'],
            'lng' => $data['lng'],
        ]);


        return response()->json(['ok' => true, 'message' => 'Location saved']);
    }
}
