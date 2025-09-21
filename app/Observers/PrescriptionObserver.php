<?php

namespace App\Observers;

use App\Models\Prescription;

class PrescriptionObserver
{
    public function creating(Prescription $p): void
    {
        if (!$p->code) {
            $p->code = 'RX-' . now()->format('Y') . '-' . str_pad((string) (Prescription::max('id') + 1), 6, '0', STR_PAD_LEFT);
        }
    }
}
