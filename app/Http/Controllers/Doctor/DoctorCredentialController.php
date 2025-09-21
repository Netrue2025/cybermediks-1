<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\DoctorCredential;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DoctorCredentialController extends Controller
{
    public function index()
    {
        $docs = DoctorCredential::where('doctor_id', Auth::id())
            ->orderByDesc('created_at')->get();
        return view('doctor.credentials.index', compact('docs'));
    }

    public function listFragment()
    {
        $docs = DoctorCredential::where('doctor_id', Auth::id())
            ->orderByDesc('created_at')->get();
        return view('doctor.credentials._list', compact('docs'))->render();
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => ['required', 'string', Rule::in(['Medical License', 'Board Certification', 'ID / Passport'])],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'], // 5MB
        ]);

        $user = Auth::user();
        $file = $request->file('file');

        $path = $file->store("credentials/{$user->id}", 'public');

        $doc = DoctorCredential::create([
            'doctor_id' => $user->id,
            'type'      => $request->input('type'),
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime'      => $file->getClientMimeType(),
            'size'      => $file->getSize(),
            'status'    => 'pending',
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Credential uploaded',
        ]);
    }

    public function download(DoctorCredential $credential)
    {
        abort_unless($credential->doctor_id === Auth::id(), 403);
        // Stream or download from public disk
        $path = $credential->file_path;
        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }
        // Inline for images/pdf, or force download
        return Storage::disk('public')->download($path, $credential->file_name);
    }

    public function destroy(DoctorCredential $credential)
    {
        abort_unless($credential->doctor_id === Auth::id(), 403);
        Storage::disk('public')->delete($credential->file_path);
        $credential->delete();

        return response()->json(['status' => 'success', 'message' => 'Credential removed']);
    }
}
