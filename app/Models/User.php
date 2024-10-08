<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'phone_number',
        'email',
        'school_id',
        'user_type',
        'password',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function ownedSchool()
    {
        return $this->hasOne(School::class, 'owner_id');
    }

}

