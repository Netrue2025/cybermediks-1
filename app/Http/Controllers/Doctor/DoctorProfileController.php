<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\DoctorProfile;
use App\Models\DoctorSpecialty;
use App\Models\Specialty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class DoctorProfileController extends Controller
{
    protected function getProfile(): DoctorProfile
    {
        return DoctorProfile::firstOrCreate(
            ['doctor_id' => Auth::id()],
            ['title' => null, 'bio' => null, 'is_available' => false, 'consult_fee' => 0, 'avg_duration' => 15]
        );
    }

    public function show()
    {
        $profile = DoctorProfile::firstOrCreate(['doctor_id' => Auth::id()]);
        $allSpecialties = Specialty::orderBy('name')->get(['id', 'name']);
        $selectedSpecialtyIds = DoctorSpecialty::where('doctor_id', Auth::id())->pluck('specialty_id')->all();
        $countries = Country::all();
        return view('doctor.profile', compact('profile', 'allSpecialties', 'selectedSpecialtyIds', 'countries'));
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
            'country_id'    => ['nullable', 'string', 'exists:countries,id'],
            'address'    => ['nullable', 'string', 'max:255'],
        ]);

        $user->fill([
            'first_name'    => $data['first_name'],
            'last_name'     => $data['last_name'],
            'phone'   => $data['phone'] ?? null,
            'gender'  => $data['gender'] ?? null,
            'dob'     => $data['dob'] ?? null,
            'country_id' => $data['country_id'] ?? null,
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

    public function availability(Request $request)
    {
        $data = $request->validate(['is_available' => ['required', 'boolean']]);

        $profile = $this->getProfile();
        $profile->update(['is_available' => (bool)$data['is_available']]);

        return response()->json([
            'status'  => 'success',
            'message' => $profile->is_available ? 'You are now available.' : 'You are set to unavailable.',
            'profile' => ['is_available' => $profile->is_available],
        ]);
    }

    public function quickUpdate(Request $request)
    {
        $data = $request->validate([
            'title'        => ['nullable', 'string', 'max:120'],
            'meeting_link'        => ['nullable', 'url', 'max:120'],
            // 'consult_fee'  => ['nullable', 'numeric', 'min:0', 'max:100000'],
            // 'avg_duration' => ['nullable', 'integer', 'min:5', 'max:240'], // minutes
            'specialty_ids' => ['array'],            // NEW
            'specialty_ids.*' => ['integer', 'exists:specialties,id'],
        ]);

        $profile = $this->getProfile();

        if ($request->has('meeting_link') && $request->meeting_link != '') {
            $now = now();
        } else {
            $now = null;
        }

        $profile->update(array_filter([
            'title'        => $data['title']        ?? null,
            'meeting_link' => $data['meeting_link']        ?? null,
            'link_updated_at' => $now,
            // 'consult_fee'  => $data['consult_fee']  ?? null,
            // 'avg_duration' => $data['avg_duration'] ?? null,
        ], fn($v) => !is_null($v)));

        if ($request->has('specialty_ids')) {
            $docId = $profile->doctor_id;

            DB::transaction(function () use ($docId, $data) {
                DoctorSpecialty::where('doctor_id', $docId)->delete();
                if (!empty($data['specialty_ids'])) {
                    $rows = array_map(fn($sid) => [
                        'doctor_id' => $docId,
                        'specialty_id' => $sid
                    ], $data['specialty_ids']);
                    DoctorSpecialty::insert($rows);
                }
            });
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Profile updated',
            'profile' => [
                'title'        => $profile->title,
                // 'consult_fee'  => (string)$profile->consult_fee,
                // 'avg_duration' => (int)$profile->avg_duration,
            ],
        ]);
    }
}
