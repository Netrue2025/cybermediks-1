<?php

namespace App\Http\Controllers\Labtech;

use App\Http\Controllers\Controller;
use App\Models\LabworkRequest;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LabtechLabworkController extends Controller
{
    public function index(Request $r)
    {
        $me = $r->user()->id;
        $q = trim((string)$r->get('q', ''));
        $status = (string)$r->get('status', ''); // optional filter

        $labworks = LabworkRequest::with(['patient'])
            ->where('labtech_id', $me)
            ->when($q !== '', function ($w) use ($q) {
                $w->where('code', 'like', "%{$q}%")
                    ->orWhere('lab_type', 'like', "%{$q}%")
                    ->orWhere('address', 'like', "%{$q}%");
            })
            ->when($status !== '', fn($w) => $w->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('labtech.labworks.index', compact('labworks', 'q', 'status'));
    }

    public function show(Request $r, LabworkRequest $lab)
    {
        abort_unless($lab->labtech_id === $r->user()->id, 403);
        $lab->load('patient');
        return view('labtech.labworks.show', compact('lab'));
    }

    public function accept(Request $r, LabworkRequest $lab)
    {
        abort_unless($lab->labtech_id === $r->user()->id, 403);
        if ($lab->status !== 'pending') return back()->withErrors(['msg' => 'Only pending requests can be accepted.']);
        $lab->update(['status' => 'accepted', 'rejection_reason' => null]);
        return back()->with('ok', 'Accepted.');
    }

    public function reject(Request $r, LabworkRequest $lab)
    {
        abort_unless($lab->labtech_id === $r->user()->id, 403);
        if ($lab->status !== 'pending') return back()->withErrors(['msg' => 'Only pending requests can be rejected.']);
        $data = $r->validate(['reason' => 'nullable|string|max:500']);
        $lab->update(['status' => 'rejected', 'rejection_reason' => $data['reason'] ?? null]);
        return back()->with('ok', 'Rejected.');
    }

    public function schedule(Request $r, LabworkRequest $lab)
    {
        abort_unless($lab->labtech_id === $r->user()->id, 403);
        $r->validate(['scheduled_at' => 'required|date']);
        if (!in_array($lab->status, ['accepted', 'scheduled'], true)) {
            return back()->withErrors(['msg' => 'You can schedule only after acceptance.']);
        }
        $lab->update(['scheduled_at' => $r->scheduled_at, 'status' => 'scheduled']);
        return back()->with('ok', 'Scheduled.');
    }

    public function setPrice(Request $r, LabworkRequest $lab)
    {
        abort_unless($lab->labtech_id === $r->user()->id, 403);
        $r->validate(['price' => 'required|numeric|min:0']);
        $lab->update(['price' => $r->price]);
        return back()->with('ok', 'Price saved.');
    }

    public function start(Request $r, LabworkRequest $lab)
    {
        abort_unless($lab->labtech_id === $r->user()->id, 403);
        if (!in_array($lab->status, ['scheduled', 'accepted'], true)) {
            return back()->withErrors(['msg' => 'Start only from scheduled/accepted.']);
        }
        $lab->update(['status' => 'in_progress']);
        return back()->with('ok', 'Marked in progress.');
    }

    public function uploadResults(Request $r, LabworkRequest $lab)
    {
        abort_unless($lab->labtech_id === $r->user()->id, 403);
        $data = $r->validate([
            'file'  => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,heic,doc,docx,xls,xlsx', 'max:20480'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        // store in public disk
        $path = $r->file('file')->store('lab-results', 'public');

        // delete previous if any
        if ($lab->results_path && Storage::disk('public')->exists($lab->results_path)) {
            Storage::disk('public')->delete($lab->results_path);
        }

        $lab->update([
            'results_path'          => $path,
            'results_original_name' => $r->file('file')->getClientOriginalName(),
            'results_mime'          => $r->file('file')->getClientMimeType(),
            'results_size'          => $r->file('file')->getSize(),
            'results_uploaded_at'   => now(),
            'results_notes'         => $data['notes'] ?? null,
            // auto-bump status but stop before completed
            'status'                => 'results_uploaded',
        ]);

        return back()->with('ok', 'Results uploaded (not yet completed).');
    }

    public function clearResults(Request $r, LabworkRequest $lab)
    {
        abort_unless($lab->labtech_id === $r->user()->id, 403);
        if ($lab->results_path && Storage::disk('public')->exists($lab->results_path)) {
            Storage::disk('public')->delete($lab->results_path);
        }
        $lab->update([
            'results_path' => null,
            'results_original_name' => null,
            'results_mime' => null,
            'results_size' => null,
            'results_uploaded_at' => null,
            'results_notes' => null,
            'status' => in_array($lab->status, ['results_uploaded'], true) ? 'in_progress' : $lab->status,
        ]);
        return back()->with('ok', 'Results cleared.');
    }

    public function complete(Request $r, LabworkRequest $lab)
    {
        abort_unless($lab->labtech_id === $r->user()->id, 403);

        // must upload results first
        if (!$lab->results_path) {
            return back()->withErrors(['msg' => 'Upload results before marking as completed.']);
        }

        // only allow completion from these states
        if (!in_array($lab->status, ['results_uploaded', 'in_progress', 'scheduled', 'accepted'], true)) {
            return back()->withErrors(['msg' => 'Invalid state for completion.']);
        }

        // require a price to settle wallets
        if (is_null($lab->price) || (float)$lab->price <= 0) {
            return back()->withErrors(['msg' => 'Set a valid price before completing.']);
        }

        try {
            DB::transaction(function () use ($lab) {
                // Refresh row for safety and lock it
                $lab->refresh();

                // Guard again inside the TX (prevents double-charge on rapid clicks)
                if (!in_array($lab->status, ['results_uploaded', 'in_progress', 'scheduled', 'accepted'], true)) {
                    throw new \RuntimeException('Invalid state for completion.');
                }

                $fee      = (float) $lab->price;
                $patient  = $lab->patient;   // ensure relation exists on model
                $labtech  = $lab->labtech;   // ensure relation exists on model

                // Move to completed first (so re-entrancy fails next time)
                $lab->update([
                    'status'       => 'completed',
                    'completed_at' => now(), // ok if column exists; otherwise remove this line
                ]);

                if ($fee > 0 && $patient && $labtech) {
                    // Charge patient
                    $patient->wallet_balance -= $fee;
                    $patient->save();

                    // Pay labtech
                    $labtech->wallet_balance += $fee;
                    $labtech->save();

                    $ref = 'LAB-' . now()->format('YmdHis') . '-' . $lab->id;

                    // Ledger entries
                    WalletTransaction::create([
                        'user_id'   => $patient->id,
                        'amount'    => -$fee,
                        'currency'  => 'NGN',
                        'type'      => 'debit',
                        'reference' => $ref . '-DEBIT',
                        'purpose'   => "Labwork fee for request ID {$lab->id}",
                    ]);

                    WalletTransaction::create([
                        'user_id'   => $labtech->id,
                        'amount'    => $fee,
                        'currency'  => 'NGN',
                        'type'      => 'credit',
                        'reference' => $ref . '-CREDIT',
                        'purpose'   => "Labwork fee received for request ID {$lab->id}",
                    ]);
                }
            });
        } catch (\Throwable $e) {
            return back()->withErrors(['msg' => $e->getMessage() ?: 'Failed to complete labwork.']);
        }

        return back()->with('ok', 'Labwork marked completed and payment settled.');
    }
}
