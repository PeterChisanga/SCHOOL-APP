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
}
