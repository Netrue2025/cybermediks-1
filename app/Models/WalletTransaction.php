<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $fillable = ['user_id', 'type', 'amount', 'currency', 'purpose', 'reference', 'meta', 'status'];
    protected $casts = ['amount' => 'decimal:2', 'meta' => 'array'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser(Builder $q, int $userId)
    {
        return $q->where('user_id', $userId);
    }
    public function scopeLatestFirst(Builder $q)
    {
        return $q->orderByDesc('created_at');
    }

    // Helpers
    public function getIsCreditAttribute(): bool
    {
        return $this->type === 'credit';
    }
    public function getSignedAmountAttribute(): string
    {
        $sign = $this->is_credit ? '+' : '-';
        return $sign . number_format((float)$this->amount, 2, '.', '');
    }
}
