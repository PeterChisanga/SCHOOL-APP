<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\School;
use App\Models\InventoryCategory;

class InventoryCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run(): void
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
            'Other'
        ];

        $schools = School::all();

        foreach ($schools as $school) {
            foreach ($defaultCategories as $category) {
                InventoryCategory::firstOrCreate([
                    'school_id' => $school->id,
                    'name'      => $category,
                ]);
            }
        }
    }
}
