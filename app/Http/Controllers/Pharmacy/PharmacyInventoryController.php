<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\PharmacyProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class PharmacyInventoryController extends Controller
{
    public function show(Request $request)
    {
        $user = Auth::user();
        $profile = $user->pharmacyProfile; // assumes relation exists
        $path = $profile?->inventory_path; // e.g. "inventories/{id}/inventory.csv"

        $headers = [];
        $rows    = [];
        $fileMeta = null;

        if ($path && Storage::exists($path)) {
            // Gather file meta
            $size = Storage::size($path);
            $mtime = Storage::lastModified($path);
            $fileMeta = [
                'path' => $path,
                'size' => $size,
                'updated_at' => $mtime ? now()->createFromTimestamp($mtime) : null,
            ];

            // Read first ~200 rows safely
            $stream = Storage::readStream($path);
            if ($stream !== false) {
                $limit = 200;
                $rowIndex = 0;

                while (($data = fgetcsv($stream)) !== false) {
                    if ($rowIndex === 0) {
                        $headers = $data ?: [];
                    } else {
                        $rows[] = $data;
                        if (count($rows) >= $limit) break;
                    }
                    $rowIndex++;
                }
                fclose($stream);
            }
        }

        return view('pharmacy.inventory.index', compact('headers', 'rows', 'fileMeta'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'inventory' => ['required', 'file', 'mimes:csv,txt', 'max:10240'], // up to 10MB
        ]);

        $user = Auth::user();

        // store at a stable path so we always overwrite
        $dir  = "inventories/{$user->id}";
        Storage::makeDirectory($dir);

        $storedPath = $request->file('inventory')->storeAs($dir, 'inventory.csv');

        // persist path to profile (no new tables)
        $profile = $user->pharmacyProfile; // ensure this relation/model exists
        if (!$profile) {
            // if you never created one, you could create a minimal one
            $profile = $user->pharmacyProfile()->create([
                'hours' => null,
                'is_24_7' => false,
                'delivery_radius_km' => null,
                'inventory_path' => $storedPath,
            ]);
        } else {
            $profile->update(['inventory_path' => $storedPath]);
        }

        return redirect()
            ->route('pharmacy.inventory.show')
            ->with('ok', 'Inventory uploaded successfully.');
    }

    public function download()
    {
        $user = Auth::user();
        $path = $user->pharmacyProfile?->inventory_path;

        if (!$path || !Storage::exists($path)) {
            abort(404, 'Inventory not found.');
        }

        return Storage::download($path, 'inventory.csv');
    }

    private function cacheKey(int $pharmacyId): string
    {
        return "pharmacy_inventory_array_{$pharmacyId}";
    }
}
