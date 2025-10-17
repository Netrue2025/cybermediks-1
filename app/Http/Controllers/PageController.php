<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function onlineStore(Request $r)
    {
        $q = trim((string) $r->query('q', ''));

        $products = Product::query()
            ->when($q !== '', function ($w) use ($q) {
                $w->where(function ($x) use ($q) {
                    $x->where('name', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('updated_at')
            ->paginate(24)                 // paginate for nicer UX
            ->withQueryString();           // keep ?q= in pagination links

        return view('store', compact('products', 'q'));
    }
}
