<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Income extends Model {
    protected $fillable = [
        'school_id', 'amount', 'source', 'description', 'term', 'year', 'date'
    ];

    // Cast 'date' to a Carbon instance
    protected $casts = [
        'date' => 'date', // This automatically converts string → Carbon
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function school() {
        return $this->belongsTo(School::class);
    }
}
