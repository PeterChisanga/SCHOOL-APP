<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assessment extends Model {
    use HasFactory;

    protected $fillable = [
        'pupil_id',
        'subject_id',
        'title',
        'term',
        'assessment_date',
        'raw_mark',
        'max_mark',
        'percentage',
        'comments',
    ];

    protected $casts = [
        'assessment_date' => 'date',
    ];

    public function pupil() {
        return $this->belongsTo(Pupil::class);
    }

    public function subject() {
        return $this->belongsTo(Subject::class);
    }
}
