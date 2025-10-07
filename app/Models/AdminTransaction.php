<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminTransaction extends Model
{
    protected $fillable = ['type', 'amount', 'currency', 'purpose', 'reference', 'meta'];

    protected $casts = [
        'meta' => 'array'
    ];
}
