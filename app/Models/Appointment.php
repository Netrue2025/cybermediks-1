<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'type',
        'scheduled_at',
        'duration',
        'status',
        'price',
        'payment_status',
        'reason',
        'notes',
        'meeting_link',
        'prescription_issued'
    ];
    protected $casts = [
        'scheduled_at' => 'datetime',
        'duration' => 'int',
        'price' => 'decimal:2',
        'prescription_issued' => 'boolean'
    ];
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }
    public function conversation()
    {
        return $this->hasOne(Conversation::class);
    }
    public function prescription()
    {
        return $this->hasOne(Prescription::class);
    }
     // Scopes for filters
    public function scopeForPatient(Builder $q, int $patientId): Builder {
        return $q->where('patient_id', $patientId);
    }
    public function scopeTypeIs(Builder $q, ?string $type): Builder {
        if ($type) $q->where('type', $type);
        return $q;
    }
    public function scopeOnDate(Builder $q, ?string $ymd): Builder {
        if ($ymd) $q->whereDate('scheduled_at', $ymd);
        return $q;
    }
    public function scopeSearch(Builder $q, ?string $term): Builder {
        if (!$term) return $q;
        $like = '%'.trim($term).'%';
        return $q->where(function($qq) use($like){
            $qq->where('title', 'like', $like)
               ->orWhere('notes', 'like', $like)
               ->orWhereHas('doctor', function($dq) use($like){
                    $dq->where('first_name', 'like', $like)
                       ->orWhere('last_name', 'like', $like);
               });
        });
    }
}
