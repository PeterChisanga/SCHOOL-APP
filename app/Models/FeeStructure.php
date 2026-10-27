<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeStructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'class_id',
        'type',
        'amount',
        'term',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'amount'    => 'decimal:2',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * The class this fee applies to. Null means "all classes".
     */
    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }
}
