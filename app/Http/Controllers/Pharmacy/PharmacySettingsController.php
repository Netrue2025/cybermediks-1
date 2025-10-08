<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\PharmacyProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class PharmacySettingsController extends Controller
{
    public function show()
    {
        $profile = PharmacyProfile::firstOrCreate(['user_id' => Auth::id()], [
            'is_24_7' => false,
            'delivery_radius_km' => 0,
            'hours' => null,
            'license_no' => null
        ]);
        return view('pharmacy.settings.index', compact('profile'));
    }

    public function update(Request $r)
    {
        $profile = PharmacyProfile::firstOrCreate(['user_id' => Auth::id()]);
        $data = $r->validate([
            'is_24_7'    => 'nullable|boolean',
            'delivery_radius_km' => 'nullable|numeric|min:0|max:500',
            'hours'      => 'nullable|string|max:3000', // store JSON or free text
        ]);
        $data['is_24_7'] = (bool)($data['is_24_7'] ?? false);
        $profile->update($data);

        return back()->with('ok', 'Settings saved.');
    }

    public function updateLicense(Request $r)
    {
        $profile = PharmacyProfile::firstOrCreate(['user_id' => Auth::id()]);

        // Block updates while pending
        if ($profile->status === 'pending') {
            return back()->withErrors([
                'operating_license' => 'Your previous submission is still under review. Please wait until it is rejected before uploading again.',
            ])->withInput();
        }

        $data = $r->validate([
            'license_no'        => 'required|string|max:120',
            'operating_license' => 'required|file|mimes:pdf,jpg,jpeg,png,webp|max:5120',
        ]);

        $oldPath = $profile->operating_license;
        $path = $r->file('operating_license')->store('pharmacy/licenses', 'public');

        $profile->fill([
            'license_no'        => $data['license_no'],
            'operating_license' => $path,
            'status'            => 'pending',
            'rejection_reason'  => null,
        ])->save();

        if ($oldPath && $oldPath !== $path) {
            Storage::disk('public')->delete($oldPath);
        }

        return back()->with('ok', 'License updated and sent for review.');
    }

    public function showProfile()
    {
        return view('pharmacy.profile');
    }

    public function updateProfile(Request $r)
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
