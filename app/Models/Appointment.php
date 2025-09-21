<?php

namespace App\Models;

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
        'notes'
    ];
    protected $casts = [
        'scheduled_at' => 'datetime',
        'duration' => 'int',
        'price' => 'decimal:2'
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
}
