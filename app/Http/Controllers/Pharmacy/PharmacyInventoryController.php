<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PharmacyInventoryController extends Controller
{
    public function index(Request $r)
    {
        $q = trim((string)$r->query('q', ''));
        $low = (bool)$r->boolean('low');

        $items = InventoryItem::where('pharmacy_id', Auth::id())
            ->when($q !== '', function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%")
                    ->orWhere('unit', 'like', "%{$q}%");
            })
            ->when($low, fn($w) => $w->whereColumn('qty', '<=', 'reorder_level'))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        if ($r->ajax()) {
            return view('pharmacy.inventory._list', compact('items'))->render();
        }

        return view('pharmacy.inventory.index', compact('items', 'q', 'low'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name' => 'required|string|max:160',
            'sku' => 'nullable|string|max:80',
            'unit' => 'nullable|string|max:40',
            'price' => 'required|numeric|min:0',
            'qty' => 'required|integer|min:0',
            'reorder_level' => 'nullable|integer|min:0',
        ]);
        $data['pharmacy_id'] = Auth::id();
        $item = InventoryItem::create($data);

        return response()->json(['status' => 'ok', 'message' => 'Item added', 'item' => $item]);
    }

    public function update(Request $r, InventoryItem $item)
    {
        abort_unless($item->pharmacy_id === Auth::id(), 403);
        $data = $r->validate([
            'name' => 'sometimes|string|max:160',
            'sku' => 'sometimes|nullable|string|max:80',
            'unit' => 'sometimes|nullable|string|max:40',
            'price' => 'sometimes|numeric|min:0',
            'reorder_level' => 'sometimes|nullable|integer|min:0',
        ]);
        $item->update($data);
        return response()->json(['status' => 'ok', 'message' => 'Updated']);
    }

    public function adjust(Request $r, InventoryItem $item)
    {
        abort_unless($item->pharmacy_id === Auth::id(), 403);
        $data = $r->validate([
            'delta' => 'required|integer', // +5 or -3
        ]);
        $item->qty = max(0, (int)$item->qty + (int)$data['delta']);
        $item->save();

        return response()->json(['status' => 'ok', 'message' => 'Stock adjusted', 'qty' => $item->qty, 'low' => $item->low_stock]);
    }

    public function destroy(InventoryItem $item)
    {
        abort_unless($item->pharmacy_id === Auth::id(), 403);
        $item->delete();
        return response()->json(['status' => 'ok', 'message' => 'Deleted']);
    }
}
