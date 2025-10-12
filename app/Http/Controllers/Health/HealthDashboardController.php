<?php

namespace App\Http\Controllers\Health;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\DoctorCredential;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class HealthDashboardController extends Controller
{
    public function index()
    {
        // If your DB stores approved as "verified", use 'verified' below. 
        // If it stores "approved", change accordingly.
        $pending  = DoctorCredential::where('status', 'pending')->count();
        $approved = DoctorCredential::where('status', 'approved')->count();

        $total = User::where('role', 'doctor')->count();

        // Fetch recent credentials to display (don’t load everything)
        $credentials = DoctorCredential::with('doctor')
            ->where('status', 'pending')
            ->latest()
            ->take(20)
            ->get();

        return view('health.dashboard', compact('pending', 'approved', 'total', 'credentials'));
    }

    public function doctorIndex(Request $r)
    {
        $q = trim((string) $r->query('q'));

        $doctors = User::with(['doctorProfile:id,doctor_id,is_available,title,consult_fee,avg_duration', 'specialties'])
            ->where('role', 'doctor')
            ->when($q !== '', function ($w) use ($q) {
                $w->where(function ($x) use ($q) {
                    $x->where('first_name', 'like', "%$q%")
                        ->orWhere('last_name', 'like', "%$q%")
                        ->orWhereHas('doctorProfile', fn($p) => $p->where('title', 'like', "%$q%"));
                });
            })
            ->orderBy('first_name')
            ->paginate(20);

        $credentials = DoctorCredential::with('doctor')->where('status', 'pending')->latest()->take(10)->get();

        return view('health.doctors.index', compact('doctors', 'q', 'credentials'));
    }

    public function credentials(User $doctor)
    {
        // authorize admin as you already do
        $docs = DoctorCredential::where('doctor_id', $doctor->id)
            ->orderByDesc('created_at')
            ->get();

        return view('health.doctors._credentials', compact('doctor', 'docs'));
    }

    public function approveCredential(Request $r, $id)
    {
        // Expecting ->input('credential_id')
        $credId = (int) $r->input('credential_id');
        $cred = DoctorCredential::where('doctor_id', $id)->findOrFail($credId);
        $cred->update(['status' => 'approved']); // requires status column
        return back()->with('success', 'Credential approved');
    }

    public function rejectCredential(Request $r, $id)
    {
        $r->validate([
            'reason' => 'required|string'
        ]);
        // Expecting ->input('credential_id')
        $credId = (int) $r->input('credential_id');
        $cred = DoctorCredential::where('doctor_id', $id)->findOrFail($credId);
        $cred->update(['status' => 'rejected', 'review_notes' => $r->reason]); // requires status column
        return back()->with('success', 'Credential rejected');
    }

    public function showProfile()
    {
        $countries = Country::all();
        return view('health.profile', compact('countries'));
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
}
