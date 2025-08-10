<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'term',
        'mid_term_mark',
        'end_of_term_mark',
        'subject_id',
        'pupil_id',
        'comments',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function pupil()
    {
        return $this->belongsTo(Pupil::class);
    }
}
