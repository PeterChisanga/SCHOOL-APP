<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'school_id',
        'amount',
        'mode_of_payment',
        'payment_method',
        'status',
        'date',
        'deposit_slip_id',
        'receipt_number',
        'parent_reference',
        'proof_of_payment_path',
        'verified_by',
        'verified_at',
        'rejection_reason',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
