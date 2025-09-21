<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorTimeoff extends Model
{
    protected $fillable = ['doctor_id', 'start_at', 'end_at', 'reason'];
    protected $casts = ['start_at' => 'datetime', 'end_at' => 'datetime'];
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }
}
