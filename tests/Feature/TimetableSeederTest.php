<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\TimetableSeeder;
use App\Models\Timetable;

class TimetableSeederTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function seeded_timetables_have_no_conflicts()
    {
        // seed timetable
        $this->seed(TimetableSeeder::class);

        // We expect 3 classes, 2 teachers, multiple timetable entries
        $timetables = Timetable::all();

        // For each class/day/slot there should be only one entry
        $grouped = $timetables->groupBy(function($item) {
            return $item->class_id . '|' . $item->day . '|' . $item->time_slot_id;
        });

        foreach ($grouped as $key => $items) {
            $this->assertCount(1, $items, "Conflict detected for {$key}");
        }

        // For each teacher/day/slot there should be only one entry
        $groupedTeachers = $timetables->groupBy(function($item) {
            return $item->teacher_id . '|' . $item->day . '|' . $item->time_slot_id;
        });

        foreach ($groupedTeachers as $key => $items) {
            $this->assertCount(1, $items, "Teacher conflict detected for {$key}");
        }

        // Room duplication check: for each room/day/slot at most one
        $groupedRooms = $timetables->groupBy(function($item) {
            return ($item->room ?? '-') . '|' . $item->day . '|' . $item->time_slot_id;
        });

        foreach ($groupedRooms as $key => $items) {
            $this->assertCount(1, $items, "Room conflict detected for {$key}");
        }
    }
}

