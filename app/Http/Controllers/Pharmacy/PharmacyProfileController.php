<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PharmacyProfileController extends Controller
{
    public function show()
    {
        return view('pharmacy.profile');
    }

    public function update(Request $r)
    {
        $user = $r->user();

        $data = $r->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['nullable', 'string', 'max:100'],
            'phone'      => ['nullable', 'string', 'max:40'],
            'gender'     => ['nullable', 'in:male,female,other'],
            'dob'        => ['nullable', 'date'],
            'country'    => ['nullable', 'string', 'max:100'],
            'address'    => ['nullable', 'string', 'max:255'],
        ]);

        $user->fill([
            'first_name'    => $data['first_name'],
            'last_name'     => $data['last_name'],
            'phone'   => $data['phone'] ?? null,
            'gender'  => $data['gender'] ?? null,
            'dob'     => $data['dob'] ?? null,
            'country' => $data['country'] ?? null,
            'address' => $data['address'] ?? null,
        ])->save();

        return response()->json(['ok' => true, 'message' => 'Profile updated']);
    }

    public function updatePassword(Request $r)
    {
        $r->validate([
            'current_password' => ['required'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        $user = $r->user();

        if (!Hash::check($r->current_password, $user->password)) {
            return response()->json(['ok' => false, 'message' => 'Current password is incorrect'], 422);
        }

        $user->update(['password' => Hash::make($r->password)]);
        return response()->json(['ok' => true, 'message' => 'Password updated']);
    }
}
