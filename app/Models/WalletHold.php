<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletHold extends Model
{
    protected $fillable = ['source_user_id', 'target_user_id', 'amount', 'status', 'ref_type', 'ref_id', 'meta'];

    protected $casts = ['meta' => 'array'];
}
