<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminProductController extends Controller
{
    public function index(Request $r)
    {
        $q     = trim((string)$r->query('q', ''));
        $edit  = $r->query('edit'); // If present, we load the product for editing

        $products = Product::query()
            ->when($q !== '', function ($w) use ($q) {
                $w->where(function ($x) use ($q) {
                    $x->where('name', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%")
                        ->orWhere('link', 'like', "%{$q}%");
                });
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $editing = null;
        if ($edit) {
            $editing = Product::find($edit);
        }

        return view('admin.products.index', compact('products', 'q', 'editing'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name'        => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price'       => ['required', 'numeric', 'min:0'],
            'link'        => ['nullable', 'url', 'max:255'],
            'image'       => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
        ]);

        if ($r->hasFile('image')) {
            $data['image'] = $r->file('image')->store('products', 'public');
        }

        Product::create($data);

        return redirect()->route('admin.products.index')
            ->with('ok', 'Product created.');
    }

    public function update(Request $r, Product $product)
    {
        $data = $r->validate([
            'name'        => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price'       => ['required', 'numeric', 'min:0'],
            'link'        => ['nullable', 'url', 'max:255'],
            'image'       => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
        ]);

        // Handle new image
        if ($r->hasFile('image')) {
            $newPath = $r->file('image')->store('products', 'public');
            // delete old if exists
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $newPath;
        }

        $product->update($data);

        return redirect()->route('admin.products.index')
            ->with('ok', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('ok', 'Product deleted.');
    }
}
