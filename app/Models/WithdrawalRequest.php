<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WithdrawalRequest extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'currency',
        'status',
        'reference',
        'payout_channel',
        'bank_name',
        'bank_code',
        'account_number',
        'account_name',
        'routing_number',
        'swift_code',
        'meta',
        'approved_at',
        'paid_at',
        'rejected_at',
        'approved_by',
        'rejected_by',
        'final_amount'
    ];

    protected $casts = [
        'meta'        => 'array',
        'approved_at' => 'datetime',
        'paid_at'     => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    public function rejector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
}
