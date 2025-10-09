<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'prescription_item_id',
        'drug',
        'dose',
        'frequency',
        'days',
        'quantity',
        'directions',
        'unit_price',
        'line_total',
        'status',
        'purchased_at',
        'fulfilled_at'
    ];
    protected $casts = [
        'days' => 'int',
        'quantity' => 'int',
        'unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
        'purchased_at' => 'datetime',
        'fulfilled_at' => 'datetime'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function prescriptionItem()
    {
        return $this->belongsTo(PrescriptionItem::class);
    }
}
