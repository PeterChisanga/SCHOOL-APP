<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'pupil_id', 'subject_id', 'term',
        'mid_term_mark', 'end_of_term_mark',
        'mid_term_raw', 'mid_term_max', 'end_term_raw', 'end_term_max'
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
