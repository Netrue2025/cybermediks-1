<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class DoctorCredential extends Model
{
    protected $fillable = ['doctor_id', 'type', 'file_path', 'status', 'verified_at', 'review_notes'];
    protected $casts = ['verified_at' => 'datetime'];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function getUrlAttribute(): string
    {
        // public disk
        return Storage::disk('public')->url($this->file_path);
    }

    public function scopeWhereDoctorCountry($q, $countryId)
    {
        return $q->whereHas('doctor', fn($u) => $u->where('country_id', $countryId));
    }

    public function scopeWhereDoctorHospital($q, $hospitalId)
    {
        return $q->whereHas('doctor', fn($u) => $u->where('hospital_id', $hospitalId));
    }
}
