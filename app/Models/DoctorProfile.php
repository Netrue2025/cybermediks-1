<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorProfile extends Model
{
    protected $fillable = ['doctor_id', 'title', 'bio', 'is_available', 'consult_fee', 'avg_duration', 'meeting_link', 'link_updated_at', 'lat', 'lng'];
    protected $casts = ['is_available' => 'bool', 'consult_fee' => 'decimal:2', 'avg_duration' => 'int', 'lat' => 'decimal:7', 'lng' => 'decimal:7', 'link_updated_at' => 'datetime'];
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }
}
