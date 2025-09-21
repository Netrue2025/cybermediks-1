<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorSpecialty extends Model
{
    protected $table = 'doctor_specialty';
    protected $fillable = ['doctor_id', 'specialty_id'];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }
}
