<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{

     protected $fillable = [
        'name',
        'iso2',
        'short',
        'currency_code',
        'currency_short',
        'phone_code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
