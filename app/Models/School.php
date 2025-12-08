<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'email',
        'motto',
        'phone',
        'photo',
        'owner_id',
        'is_premium',
        'subscription_expires_at',
    ];

    protected $casts = [
        'subscription_expires_at' => 'date',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function pupils()
    {
        return $this->hasMany(Pupil::class);
    }

    public function classes()
    {
        return $this->hasMany(SchoolClass::class);
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function isPremium()
    {
        return $this->is_premium && (!$this->subscription_expires_at || $this->subscription_expires_at > now());
    }
}
