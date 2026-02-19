<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryCategory extends Model
{
    use HasFactory;

     protected $fillable = [
        'name',
        'description',
        'school_id'
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'category_id');
    }
}
