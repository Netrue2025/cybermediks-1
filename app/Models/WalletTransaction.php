<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $fillable = ['user_id', 'type', 'amount', 'currency', 'purpose', 'reference', 'meta'];
    protected $casts = ['amount' => 'decimal:2', 'meta' => 'array'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
