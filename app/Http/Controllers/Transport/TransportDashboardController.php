<?php

namespace App\Http\Controllers\Transport;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\PharmacyProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class TransportDashboardController extends Controller
{

    public function index()
    {
        // If your app uses 'verified' instead of 'approved', change accordingly.
        $counts = PharmacyProfile::selectRaw("SUM(status = 'pending')  as pending")
            ->selectRaw("SUM(status = 'approved') as approved")
            ->first();

        $pending  = (int) ($counts->pending ?? 0);
        $approved = (int) ($counts->approved ?? 0);
        $total    = User::where('role', 'pharmacy')->count();

        // Only pharmacies with a pending profile submission
        $pharmacies = User::select('id', 'first_name', 'last_name', 'email')
            ->where('role', 'pharmacy')
            ->whereHas('pharmacyProfile', function ($q) {
                $q->where('status', 'pending');
            })
            ->with(['pharmacyProfile' => function ($q) {
                $q->select('id', 'user_id', 'license_no', 'operating_license', 'status', 'created_at', 'updated_at');
            }])
            ->latest('id')
            ->get(); // or ->get()

        return view('transport.dashboard', compact('pending', 'approved', 'total', 'pharmacies'));
    }

    public function pharmacyIndex(Request $r)
    {
        $q = trim((string)$r->query('q'));

        $pharmacies = User::with(['pharmacyProfile'])
            ->where('role', 'pharmacy')
            ->when($q !== '', function ($w) use ($q) {
                $w->where(function ($x) use ($q) {
                    $x->where('first_name', 'like', "%$q%")
                        ->orWhere('last_name', 'like', "%$q%")
                        ->orWhere('email', 'like', "%$q%");
                });
            })
            ->orderBy('first_name')
            ->paginate(20);

        return view('transport.pharmacies.index', compact('pharmacies', 'q'));
    }


    public function showProfile()
    {
        $countries = Country::all();
        return view('transport.profile', compact('countries'));
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

    // Approve
    public function approveLicense(User $pharmacy)
    {
        $profile = $pharmacy->pharmacyProfile;
        abort_unless($profile, 404);

        $profile->update([
            'status' => 'approved',
            'rejection_reason' => null,
        ]);

        return back()->with('ok', 'License approved.');
    }

    // Reject
    public function rejectLicense(Request $r, User $pharmacy)
    {
        $data = $r->validate([
            'reason' => ['required', 'string', 'min:5', 'max:2000'],
        ]);

        $profile = $pharmacy->pharmacyProfile;
        abort_unless($profile, 404);

        $profile->update([
            'status' => 'rejected',
            'rejection_reason' => $data['reason'],
        ]);

        return back()->with('ok', 'License rejected.');
    }
}
