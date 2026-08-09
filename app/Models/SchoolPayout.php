<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolPayout extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'amount',
        'reference',
        'notes',
        'paid_by',
        'paid_at',
    ];

    protected $casts = [
        'paid_at' => 'date',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }
}
