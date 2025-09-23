<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    protected $fillable = ['appointment_id', 'patient_id', 'doctor_id', 'code', 'status', 'refills', 'notes', 'pharmacy_id', 'dispatcher_id', 'dispense_status', 'total_amount'];
    protected $casts = ['refills' => 'int'];
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function pharmacy()
    {
        return $this->belongsTo(User::class, 'pharmacy_id');
    }

    public function items()
    {
        return $this->hasMany(PrescriptionItem::class);
    }

    public function scopeForPatient(Builder $q, int $patientId): Builder
    {
        return $q->where('patient_id', $patientId);
    }
    public function scopeStatusIs(Builder $q, ?string $status): Builder
    {
        return $status ? $q->where('status', $status) : $q;
    }
    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        if (!$term) return $q;
        $like = '%' . trim($term) . '%';
        return $q->where(function ($qq) use ($like) {
            $qq->where('code', 'like', $like)
                ->orWhere('notes', 'like', $like)
                ->orWhereHas('doctor', function ($dq) use ($like) {
                    $dq->where('first_name', 'like', $like)->orWhere('last_name', 'like', $like);
                })
                ->orWhereHas('items', function ($iq) use ($like) {
                    $iq->where('medicine', 'like', $like);
                });
        });
    }
}
