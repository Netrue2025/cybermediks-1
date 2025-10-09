<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'prescription_id',
        'patient_id',
        'pharmacy_id',
        'status',
        'items_subtotal',
        'delivery_fee',
        'grand_total',
        'currency',
        'meta',
        'dispatcher_id',
        'dispatcher_price',
    ];
    protected $casts = ['items_subtotal' => 'decimal:2', 'delivery_fee' => 'decimal:2', 'dispatcher_price' => 'decimal:2', 'grand_total' => 'decimal:2', 'meta' => 'array'];

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
    public function pharmacy()
    {
        return $this->belongsTo(User::class, 'pharmacy_id');
    }

    public function dispatcher()
    {
        return $this->belongsTo(User::class, 'dispatcher_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Handy computed states
    public function getAllItemsPurchasedAttribute(): bool
    {
        return $this->items->count() > 0
            && $this->items->every(fn($i) => $i->status === 'purchased');
    }
}
