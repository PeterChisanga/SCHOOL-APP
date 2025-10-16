<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassModel extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'name',
        'school_id',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function pupils()
    {
        return $this->hasMany(Pupil::class, 'class_id');
    }

    public function teachers() {
        return $this->belongsToMany(Teacher::class, 'class_teacher');
    }

}
