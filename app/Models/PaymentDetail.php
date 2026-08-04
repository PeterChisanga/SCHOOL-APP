<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentDetail extends Model
{
    protected $fillable = [
        'school_id',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'bank_branch',
        'bank_swift_code',
        'mobile_money_provider',
        'mobile_money_number',
        'mobile_money_account_name',
        'payment_instructions',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}