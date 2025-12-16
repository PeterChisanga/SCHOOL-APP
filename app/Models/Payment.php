<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'amount_paid',
        'type',  // e.g., 'School Fees', 'Lunch Fees', 'Transport Fees'
        'school_id',
        'pupil_id',
        'term',  // e.g., 'Term 1', 'Term 2', etc.
        'balance',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function pupil()
    {
        return $this->belongsTo(Pupil::class);
    }

    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }
}

