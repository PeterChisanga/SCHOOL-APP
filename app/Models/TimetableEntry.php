<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimetableEntry extends Model
{
    use HasFactory;

    public const DAYS = [
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
        7 => 'Sunday',
    ];

    public const TERMS = ['Term 1', 'Term 2', 'Term 3'];

    protected $fillable = [
        'school_id',
        'class_id',
        'term',
        'day',
        'period',
        'subject_id',
        'teacher_id',
        'published_at',
    ];

    protected $casts = [
        'day'          => 'integer',
        'period'       => 'integer',
        'published_at' => 'datetime',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    /** Teachers only ever see a confirmed timetable. */
    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }

    public static function dayName(?int $day): string
    {
        return self::DAYS[$day] ?? '';
    }
}
