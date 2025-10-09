<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\PharmacyProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class PharmacyInventoryController extends Controller
{
    public function show()
    {
        $profile = PharmacyProfile::firstOrCreate(['user_id' => Auth::id()]);
        return view('pharmacy.inventory.index', compact('profile'));
    }

    public function upload(Request $r)
    {
        $data = $r->validate([
            'inventory' => 'required|file|mimetypes:text/plain,text/csv,text/tsv,text/comma-separated-values,text/plaintext,text/x-csv,application/vnd.ms-excel|max:5120',
        ]);

        $user = Auth::user();
        $profile = PharmacyProfile::firstOrCreate(['user_id' => $user->id]);

        $path = $r->file('inventory')->storeAs(
            "pharmacies/{$user->id}",
            'inventory.csv'
        );

        $profile->update(['inventory_path' => $path]);

        // Invalidate any cache of this pharmacy’s parsed inventory
        Cache::forget($this->cacheKey($user->id));

        return back()->with('ok', 'Inventory CSV uploaded.');
    }

    private function cacheKey(int $pharmacyId): string
    {
        return "pharmacy_inventory_array_{$pharmacyId}";
    }
}
