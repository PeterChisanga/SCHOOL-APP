<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'amount',
        'mode_of_payment',
        'date',
        'deposit_slip_id',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
