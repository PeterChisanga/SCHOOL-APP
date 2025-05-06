<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pupil extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'religion',
        'date_of_birth',
        'admission_date',
        'blood_group',
        'health_conditions',
        'school_id',
        'class_id',
    ];

    protected static function boot() {
        parent::boot();

        static::deleting(function ($pupil) {
            if ($pupil->parent) {
                $pupil->parent->delete();
            }

            $pupil->examResults()->delete();

            $pupil->payments()->delete();
        });
    }

    public function parent()
    {
        return $this->hasOne(ParentModel::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function examResults()
    {
        return $this->hasMany(ExamResult::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    protected $casts = [
        'date_of_birth' => 'date',
        'admission_date' => 'date',
    ];
}
