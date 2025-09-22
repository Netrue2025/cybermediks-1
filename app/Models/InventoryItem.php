<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    protected $fillable = [
        'pharmacy_id',
        'name',
        'sku',
        'unit',
        'price',
        'qty',
        'reorder_level',
        'meta'
    ];
    protected $casts = ['price' => 'decimal:2', 'qty' => 'int', 'reorder_level' => 'int', 'meta' => 'array'];

    public function pharmacy()
    {
        return $this->belongsTo(User::class, 'pharmacy_id');
    }

    // helpers
    public function getLowStockAttribute(): bool
    {
        return $this->qty <= ($this->reorder_level ?? 0);
    }
}
