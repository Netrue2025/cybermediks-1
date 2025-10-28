<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Controller;
use App\Mail\UniversalMail;
use App\Models\Country;
use App\Models\DoctorCredential;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class HospitalDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $pending = DoctorCredential::where('status', 'pending')
            ->whereHas('doctor', fn($q) => $q->where('hospital_id', $user->id))
            ->count();

        $approved = DoctorCredential::where('status', 'approved')
            ->whereHas('doctor', fn($q) => $q->where('hospital_id', $user->id))
            ->count();

        $total = User::where('role', 'doctor')
            ->where('hospital_id', $user->id)
            ->count();

        $credentials = DoctorCredential::with('doctor')
            ->where('status', 'pending')
            ->whereHas('doctor', fn($q) => $q->where('hospital_id', $user->id))
            ->latest()
            ->take(20)
            ->get();
        $countries = Country::all();

        return view('hospital.dashboard', compact('pending', 'approved', 'total', 'credentials', 'countries'));
    }


    public function doctorIndex(Request $r)
    {
        $q         = trim((string) $r->query('q'));
        $user = Auth::user();

        $doctors = User::with([
            'doctorProfile:id,doctor_id,is_available,title,consult_fee,avg_duration',
            'specialties'
        ])
            ->doctors()
            ->where('hospital_id', $user->id)
            ->when($q !== '', function ($w) use ($q) {
                $w->where(function ($x) use ($q) {
                    $x->where('first_name', 'like', "%{$q}%")
                        ->orWhere('last_name', 'like', "%{$q}%")
                        ->orWhereHas('doctorProfile', fn($p) => $p->where('title', 'like', "%{$q}%"));
                });
            })
            ->orderBy('first_name')
            ->paginate(20);

        $credentials = DoctorCredential::with('doctor')
            ->where('status', 'pending')
            ->whereDoctorHospital($user->id)
            ->latest()
            ->take(10)
            ->get();

        return view('hospital.doctors.index', compact('doctors', 'q', 'credentials'));
    }

    public function credentials(User $doctor)
    {
        abort_unless($doctor->hospital_id === Auth::id(), 403);

        $docs = DoctorCredential::where('doctor_id', $doctor->id)
            ->orderByDesc('created_at')
            ->get();

        return view('hospital.doctors._credentials', compact('doctor', 'docs'));
    }

    public function showProfile()
    {
        $countries = Country::all();
        return view('hospital.profile', compact('countries'));
    }

    public function updateProfile(Request $r)
    {
        $user = $r->user();

        $data = $r->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['nullable', 'string', 'max:100'],
            'phone'      => ['nullable', 'string', 'max:40'],
            'facility_name' => ['nullable', 'string', 'max:255'],
            'gender'     => ['nullable', 'in:male,female,other'],
            'dob'        => ['nullable', 'date'],
            'country_id'    => ['nullable', 'string', 'exists:countries,id'],
            'address'    => ['nullable', 'string', 'max:255'],
            'address_building_no'    => ['nullable', 'string', 'max:100'],
            'state'    => ['nullable', 'string', 'max:100'],
            'city'    => ['nullable', 'string', 'max:100'],
        ]);

        $user->fill([
            'first_name'    => $data['first_name'],
            'last_name'     => $data['last_name'],
            'phone'   => $data['phone'] ?? null,
            'facility_name' => $data['facility_name'] ?? null,
            'gender'  => $data['gender'] ?? null,
            'dob'     => $data['dob'] ?? null,
            'country_id' => $data['country_id'] ?? null,
            'state' => $data['state'] ?? null,
            'city' => $data['city'] ?? null,
            'address' => $data['address'] ?? null,
            'address_building_no' => $data['address_building_no'] ?? null,
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

    public function register(Request $r)
    {
        $data = $r->validate([
            'first_name'  => ['required', 'string', 'max:255'],
            'last_name'   => ['required', 'string', 'max:255'],
            'email'       => ['required', 'email', 'max:255', 'unique:users,email'],
            'country_id'  => ['required', 'string', 'exists:countries,id'],
            // user won’t set a password now
        ]);

        // 1) Generate a strong temporary password (Laravel 10+ has Str::password)
        // If your version doesn’t have Str::password(), see the fallback below.
        $tempPassword = Str::password(12); // length 12, includes mixed case, numbers, symbols

        $user = User::create([
            'first_name'  => $data['first_name'],
            'last_name'   => $data['last_name'],
            'email'       => strtolower($data['email']),
            'role'        => 'doctor',
            'hospital_id' => Auth::id(),
            'country_id'  => $data['country_id'],
            'password'    => Hash::make($tempPassword),
            // OPTIONAL (recommended): force password change on first login
            // 'must_change_password' => true,
        ]);

        $name = trim($user->first_name . ' ' . $user->last_name);
        $loginUrl = url('/login');

        // 2) Email credentials using your UniversalMail + the universal template
        Mail::to($user->email)->send(new UniversalMail([
            'subject'      => 'Your CyberMediks Doctor Account',
            'view'         => 'emails.universal',
            'title'        => 'Welcome to CyberMediks',
            'greeting'     => "Hello {$name},",
            'intro'        => "Your doctor account has been created. Use the temporary password below to sign in. For security, please change it immediately after login.",
            'password'         => $tempPassword,          // shows in the big code block
            'action_text'  => 'Login to CyberMediks',
            'action_url'   => $loginUrl,
            'outro'        => "If you didn’t create this account, please ignore this email or contact support.",
            'footer'       => "CyberMediks • " . config('app.url'),
        ]));

        return response()->json([
            'ok'      => true,
            'message' => 'Account created. Temporary password emailed to the doctor.',
        ]);
    }
}
