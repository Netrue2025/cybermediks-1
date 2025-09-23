<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Specialty;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminSpecialtiesController extends Controller
{
    public function index(Request $r)
    {
        $q = trim((string)$r->query('q'));
        $items = Specialty::when($q !== '', fn($w) => $w->where('name', 'like', "%$q%"))
            ->orderBy('name')
            ->paginate(30);
        return view('admin.specialties.index', compact('items', 'q'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name'  => 'required|string|max:120',
            'icon'  => 'nullable|string|max:120',                    // e.g. "fa-solid fa-heart-pulse"
            'color' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'], // hex with #
        ]);

        $slug = Str::slug($data['name']);
        // ensure slug uniqueness
        $base = $slug;
        $i = 1;
        while (Specialty::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        Specialty::create([
            'name'  => $data['name'],
            'icon'  => $data['icon'] ?: 'fa-solid fa-stethoscope',
            'color' => $data['color'] ?: '#64748b',
            'slug'  => $slug,
        ]);

        return back()->with('success', 'Specialty saved');
    }

    public function update(Request $r, Specialty $specialty)
    {
        $data = $r->validate([
            'name'  => 'required|string|max:120',
            'icon'  => 'nullable|string|max:120',
            'color' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
        ]);

        // If name changes, re-slug (and keep unique)
        if ($specialty->name !== $data['name']) {
            $slug = Str::slug($data['name']);
            $base = $slug;
            $i = 1;
            while (Specialty::where('slug', $slug)->where('id', '<>', $specialty->id)->exists()) {
                $slug = $base . '-' . $i++;
            }
            $specialty->slug = $slug;
        }

        $specialty->name  = $data['name'];
        $specialty->icon  = $data['icon'] ?: 'fa-solid fa-stethoscope';
        $specialty->color = $data['color'] ?: '#64748b';
        $specialty->save();

        return back()->with('success', 'Specialty updated');
    }

    public function destroy($id)
    {
        Specialty::findOrFail($id)->delete();
        return back()->with('success', 'Specialty removed');
    }
}
