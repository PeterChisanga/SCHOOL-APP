<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendance';

    protected $fillable = [
        'school_id',
        'class_id',
        'pupil_id',
        'date',
        'status',
        'teacher_id',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function pupil()
    {
        return $this->belongsTo(Pupil::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
