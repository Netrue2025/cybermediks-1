<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PharmacyProfile extends Model
{
    protected $fillable = [
        'user_id',
        'license_no',
        'operating_license',
        'hours',
        'is_24_7',
        'inventory_path',
        'delivery_radius_km',
        'status',
        'rejection_reason'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeWhereUserCountry($q, $countryId)
    {
        return $q->whereHas('user', fn($u) => $u->where('country_id', $countryId));
    }
}
