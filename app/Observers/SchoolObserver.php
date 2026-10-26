<?php
namespace App\Observers;

use App\Models\School;
use App\Models\InventoryCategory;

class SchoolObserver
{
    public function created(School $school): void
    {
        $defaultCategories = [
            'Furniture',
            'Stationery',
            'ICT Equipment',
            'Lab Equipment',
            'Science Equipment',
            'Sports Equipment',
            'Musical Instruments',
            'Art Supplies',
            'Cleaning Supplies',
            'Office Supplies',
            'Maintenance Tools',
            'Other',
        ];

        foreach ($defaultCategories as $category) {
            InventoryCategory::firstOrCreate([
                'school_id' => $school->id,
                'name' => $category,
            ]);
        }
    }
}
