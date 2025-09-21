<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorSchedule extends Model
{
    protected $fillable = ['doctor_id', 'weekday', 'start_time', 'end_time', 'enabled'];
    protected $casts = ['weekday' => 'int', 'enabled' => 'bool'];
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public static function weekdays(): array
    {
        return [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun'];
    }
}
