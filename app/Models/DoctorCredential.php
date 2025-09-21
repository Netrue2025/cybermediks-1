<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorCredential extends Model
{
    protected $fillable = ['doctor_id', 'type', 'file_path', 'status', 'verified_at', 'review_notes'];
    protected $casts = ['verified_at' => 'datetime'];
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }
}
