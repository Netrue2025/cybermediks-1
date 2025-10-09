<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentDispute extends Model
{
    protected $fillable = ['appointment_id', 'patient_id', 'reason', 'status'];
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}
