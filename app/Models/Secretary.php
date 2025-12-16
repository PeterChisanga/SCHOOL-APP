<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Secretary extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'user_id',
    ];

    /**
     * Get the school associated with the teacher.
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
