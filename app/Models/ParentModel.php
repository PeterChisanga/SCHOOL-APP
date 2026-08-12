<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParentModel extends Model
{
    use HasFactory;

    protected $table = 'parents';

    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'email',
        'address',
        'pupil_id',
        'school_id',
    ];

    public function pupil()
    {
        return $this->belongsTo(Pupil::class);
    }

    /**
     * Store phone numbers in a consistent format (+260...) no matter how
     * they were entered, so searches and SMS always see the same shape.
     */
    public function setPhoneAttribute($value): void
    {
        $this->attributes['phone'] = normalizePhoneNumber($value);
    }
}
