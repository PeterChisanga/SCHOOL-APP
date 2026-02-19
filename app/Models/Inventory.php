<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'category_id',
        'item_name',
        'quantity',
        'condition',
        'location',
        'description',
        'date_added'
    ];

    protected $casts = [
        'date_added' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function category()
    {
        return $this->belongsTo(InventoryCategory::class, 'category_id');
    }

    public function activityLogs()
    {
        return $this->hasMany(InventoryActivityLog::class);
    }
}
