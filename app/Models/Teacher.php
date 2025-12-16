<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'phone',
        'email',
        'address',
        'marital_status',
        'date_of_birth',
        'admission_date',
        'qualification',
        'salary',
        'school_id',
        'class_id',
        'user_id',
    ];

    /**
     * Get the school associated with the teacher.
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the class that the teacher is associated with.
     */
    // In Teacher model
    public function classes() {
        return $this->belongsToMany(ClassModel::class, 'class_teacher', 'teacher_id', 'class_id');
    }

    /**
     * Get the class that the teacher is associated with.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
