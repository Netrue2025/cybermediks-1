<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrescriptionItem extends Model
{
    protected $fillable = ['prescription_id', 'drug', 'dose', 'frequency', 'days', 'quantity', 'directions'];
    protected $casts = ['days' => 'int', 'quantity' => 'int'];
    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }
}
