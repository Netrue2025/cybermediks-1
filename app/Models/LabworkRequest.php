<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabworkRequest extends Model
{
    protected $fillable = [
        'code',
        'patient_id',
        'labtech_id',
        'lab_type',
        'collection_method',
        'address',
        'notes',
        'preferred_at',
        'status',
        'scheduled_at',
        'price',
        'results_path',
        'results_original_name',
        'results_mime',
        'results_size',
        'results_uploaded_at',
        'results_notes',
        'rejection_reason',
    ];

    protected $casts = [
        'preferred_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'results_uploaded_at' => 'datetime',
        'price' => 'decimal:2',
    ];

    // Parties
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
    public function labtech()
    {
        return $this->belongsTo(User::class, 'labtech_id');
    }

    // Nice tiny code generator
    public static function nextCode(): string
    {
        $prefix = 'LB';
        do {
            $code = $prefix . '-' . now()->format('ymd') . '-' . strtoupper(str()->random(4));
        } while (static::where('code', $code)->exists());
        return $code;
    }

    // Simple guards
    public function canPatientCancel(): bool
    {
        return in_array($this->status, ['pending', 'accepted', 'scheduled'], true);
    }

    public function resultsReady(): bool
    {
        return !is_null($this->results_path);
    }
}
