<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpVerification extends Model
{
    protected $fillable = ['phone', 'otp', 'expires_at', 'used'];
    protected $casts    = ['expires_at' => 'datetime', 'used' => 'boolean'];
}