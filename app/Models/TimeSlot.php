<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'start_time', 'end_time', 'order', 'school_id'
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}

