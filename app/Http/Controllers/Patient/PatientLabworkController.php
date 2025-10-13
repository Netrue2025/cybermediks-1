<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\LabworkRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PatientLabworkController extends Controller
{
    public function index(Request $r)
    {
        $labworks = LabworkRequest::with(['labtech'])
            ->where('patient_id', $r->user()->id)
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('patient.labworks.index', compact('labworks'));
    }

    public function create()
    {
        $labTypes = [
            'Full Blood Count',
            'Lipid Panel',
            'Blood Glucose',
            'Liver Function Test',
            'Kidney Function Test',
            'Malaria Parasite Test',
            'COVID-19 PCR',
        ];

        $providers = User::where('role', 'labtech')
            ->select('id', 'first_name', 'last_name', 'email')
            ->orderBy('first_name')->take(100)->get();

        return view('patient.labworks.create', compact('labTypes', 'providers'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'lab_type'          => ['required', 'string', 'max:200'],
            'collection_method' => ['required', 'in:home,in_lab'],
            'address'           => ['nullable', 'string', 'max:255'],
            'notes'             => ['nullable', 'string', 'max:2000'],
            'preferred_at'      => ['nullable', 'date'],
            'labtech_id'        => ['required', 'exists:users,id'],
        ]);

        // ensure selected is a labtech
        abort_unless(User::where('id', $data['labtech_id'])->where('role', 'labtech')->exists(), 422, 'Invalid provider');

        if ($data['collection_method'] === 'home') {
            $r->validate(['address' => ['required', 'string', 'max:255']]);
        } else {
            $data['address'] = null;
        }

        $lab = LabworkRequest::create([
            'code'              => LabworkRequest::nextCode(),
            'patient_id'        => Auth::id(),
            'labtech_id'        => (int)$data['labtech_id'],
            'lab_type'          => $data['lab_type'],
            'collection_method' => $data['collection_method'],
            'address'           => $data['address'] ?? null,
            'notes'             => $data['notes'] ?? null,
            'preferred_at'      => $data['preferred_at'] ?? null,
            'status'            => 'pending',
        ]);

        return redirect()->route('patient.labworks.show', $lab)->with('ok', 'Labwork request submitted.');
    }

    public function show(LabworkRequest $lab)
    {
        abort_unless($lab->patient_id === Auth::id(), 403);
        $lab->load('labtech');
        return view('patient.labworks.show', compact('lab'));
    }

    public function cancel(LabworkRequest $lab)
    {
        abort_unless($lab->patient_id === Auth::id(), 403);
        if (! $lab->canPatientCancel()) {
            return back()->withErrors(['msg' => 'You can no longer cancel this request.']);
        }
        $lab->update(['status' => 'cancelled']);
        return back()->with('ok', 'Request cancelled.');
    }

    // Optional: AJAX search
    public function providers(Request $r)
    {
        $q = trim((string)$r->query('q', ''));
        $providers = User::where('role', 'labtech')
            ->when($q !== '', function ($w) use ($q) {
                $w->where(function ($x) use ($q) {
                    $x->where('first_name', 'like', "%{$q}%")
                        ->orWhere('last_name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->select('id', 'first_name', 'last_name', 'email')
            ->orderBy('first_name')->take(50)->get();

        return view('patient.labworks._provider_list', compact('providers'))->render();
    }

    public function assignProvider(Request $r, LabworkRequest $lab)
    {
        abort_unless($lab->patient_id === Auth::id(), 403);
        $r->validate(['labtech_id' => 'required|exists:users,id']);

        if (!in_array($lab->status, ['pending', 'accepted', 'scheduled'], true)) {
            return back()->withErrors(['msg' => 'You cannot change the provider now.']);
        }

        abort_unless(User::where('id', $r->labtech_id)->where('role', 'labtech')->exists(), 422, 'Invalid provider');

        $lab->update([
            'labtech_id' => (int)$r->labtech_id,
            'status' => 'pending',
            'rejection_reason' => null,
        ]);

        return back()->with('ok', 'Provider updated.');
    }

    public function downloadResults(LabworkRequest $lab)
    {
        abort_unless($lab->patient_id === Auth::id(), 403);
        if (!$lab->results_path || !Storage::disk('public')->exists($lab->results_path)) {
            abort(404);
        }
        return Storage::disk('public')->download(
            $lab->results_path,
            $lab->results_original_name ?: 'lab-results.pdf'
        );
    }
}
