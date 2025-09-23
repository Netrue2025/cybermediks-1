<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PharmacyProfile;
use App\Models\User;
use Illuminate\Http\Request;

class AdminPharmaciesController extends Controller
{
    public function index(Request $r)
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

        return view('admin.pharmacies.index', compact('pharmacies', 'q'));
    }

    public function profile(User $pharmacy)
    {
        // ensure it's a pharmacy
        if ($pharmacy->role !== 'pharmacy') {
            abort(404);
        }

        $profile = PharmacyProfile::where('user_id', $pharmacy->id)->first();

        return view('admin.pharmacies._profile', compact('pharmacy', 'profile'));
    }

    // Toggle 24/7
    public function toggle24(User $pharmacy)
    {
        if ($pharmacy->role !== 'pharmacy') abort(404);

        $profile = PharmacyProfile::firstOrCreate(['user_id' => $pharmacy->id]);
        $profile->is_24_7 = ! (bool) ($profile->is_24_7 ?? false);
        $profile->save();

        return back()->with('status', 'Updated 24/7 availability');
    }

    // Update radius
    public function updateRadius(Request $request, User $pharmacy)
    {
        if ($pharmacy->role !== 'pharmacy') abort(404);

        $data = $request->validate([
            'delivery_radius_km' => 'nullable|numeric|min:0|max:1000',
        ]);

        $profile = PharmacyProfile::firstOrCreate(['user_id' => $pharmacy->id]);
        $profile->delivery_radius_km = $data['delivery_radius_km'] ?? null;
        $profile->save();

        // For AJAX form we can return a simple 200
        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok']);
        }
        return back()->with('status', 'Radius updated');
    }
}
