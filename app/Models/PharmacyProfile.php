<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PharmacyProfile extends Model
{
    protected $fillable = [
        'user_id',
        'license_no',
        'hours',
        'is_24_7',
        'delivery_radius_km',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
