<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppointmentDispute;
use App\Models\WalletHold;
use App\Services\DisputeHoldService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminDisputeController extends Controller
{
    public function index(Request $r)
    {
        $q       = trim((string)$r->query('q', ''));
        $status  = $r->query('status', 'open'); // open, admin_review, resolved, (or all)
        $from    = $r->query('from'); // date
        $to      = $r->query('to');   // date

        $disputes = AppointmentDispute::with([
            'appointment.doctor:id,first_name,last_name',
            'appointment.patient:id,first_name,last_name',
        ])
            ->when($q !== '', function ($w) use ($q) {
                $w->where(function ($x) use ($q) {
                    $x->where('reason', 'like', "%{$q}%")
                        ->orWhereHas('appointment.patient', fn($p) => $p
                            ->where('first_name', 'like', "%{$q}%")
                            ->orWhere('last_name', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%"))
                        ->orWhereHas('appointment.doctor', fn($d) => $d
                            ->where('first_name', 'like', "%{$q}%")
                            ->orWhere('last_name', 'like', "%{$q}%"));
                });
            })
            ->when($status && $status !== 'all', fn($w) => $w->where('status', $status))
            ->when($from, fn($w) => $w->whereDate('created_at', '>=', $from))
            ->when($to,   fn($w) => $w->whereDate('created_at', '<=', $to))
            ->latest()
            ->paginate(20)
            ->withQueryString();


        $holdByAppt = WalletHold::whereIn(
            'ref_id',
            $disputes->pluck('appointment_id')
        )->where('ref_type', 'appointment')
            ->latest()
            ->get()
            ->keyBy('ref_id');

        return view('admin.disputes.index', compact('disputes', 'holdByAppt', 'q', 'status', 'from', 'to'));
    }

    public function show(AppointmentDispute $dispute)
    {
        $dispute->load([
            'appointment.doctor:id,first_name,last_name',
            'appointment.patient:id,first_name,last_name',
            'appointment' => fn($q) => $q->withExists(['dispute']),
        ]);

        $hold = WalletHold::where([
            'ref_type' => 'appointment',
            'ref_id'   => $dispute->appointment_id,
        ])->latest()->first(); // could be null if something went wrong

        return view('admin.disputes.show', compact('dispute', 'hold'));
    }

    public function resolve(Request $r, AppointmentDispute $dispute)
    {
        $data = $r->validate([
            'decision'      => ['required', Rule::in(['refund', 'release', 'partial'])],
            'amount_cents'  => ['nullable', 'integer', 'min:0'],   // required if partial
            'admin_notes'   => ['nullable', 'string', 'max:2000'],
        ]);

        $appointment = $dispute->appointment()->with(['patient', 'doctor'])->firstOrFail();

        // If you hold funds against this appointment/prescription, integrate here:
        // Example service calls (implement them in your app):
        // WalletService::releaseHoldToPayer($appointment)   for refund
        // WalletService::captureHold($appointment)          for release
        // WalletService::partialCaptureAndRelease($appointment, $data['amount_cents'])

        match ($data['decision']) {
            'refund'  => $this->refundAppointment($appointment, $dispute, $data['admin_notes'] ?? null),
            'release' => $this->releaseAppointment($appointment, $dispute, $data['admin_notes'] ?? null),
            'partial' => $this->partialAppointment($appointment, $dispute, (int)($data['amount_cents'] ?? 0), $data['admin_notes'] ?? null),
        };

        return back()->with('ok', 'Dispute resolved.');
    }

    protected function refundAppointment($appointment, AppointmentDispute $dispute, ?string $notes)
    {
        DisputeHoldService::refundToPatient($appointment);

        $appointment->update(['status' => 'resolved']);
        $dispute->update(['status' => 'resolved', 'admin_notes' => $notes]);
    }

    protected function releaseAppointment($appointment, AppointmentDispute $dispute, ?string $notes)
    {
        DisputeHoldService::releaseBackToDoctor($appointment);

        $appointment->update(['status' => 'resolved']);
        $dispute->update(['status' => 'resolved', 'admin_notes' => $notes]);
    }

    protected function partialAppointment($appointment, AppointmentDispute $dispute, int $amountCentsIgnored, ?string $notes)
    {
        // We ignore incoming amount for "split in 2" — service splits 50/50 with cent rounding.
        DisputeHoldService::partialSplit($appointment);

        $appointment->update(['status' => 'resolved']);
        $dispute->update([
            'status'      => 'resolved',
            'admin_notes' => trim(($notes ?? '') . "\nDecision: Partial 50/50 split."),
        ]);
    }
}
