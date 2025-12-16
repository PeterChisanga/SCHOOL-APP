<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Timetable;
use App\Models\ClassModel;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\TimeSlot;
use App\Models\User;
use App\Models\School;

class TimetableFragmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_fragment_endpoints_return_html_table_for_class_and_teacher()
    {
        // create a school using factory (factory will ensure owner exists)
        $school = School::factory()->create();

        // create and authenticate a user for any protected routes (though fragments are web routes)
        $user = User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test_fragment_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'phone_number' => '0000000000',
            'school_id' => $school->id,
            'user_type' => 'admin'
        ]);
        $this->actingAs($user);

        // Create minimal related records
        $class = ClassModel::create(["name" => "Class B", "school_id" => $school->id]);
        $teacher = Teacher::create([
            "first_name" => "Jane",
            "last_name" => "Smith",
            "gender" => 'female',
            "phone" => '0700000001',
            "school_id" => $school->id,
            "class_id" => $class->id,
            "marital_status" => 'single',
            "date_of_birth" => '1990-01-01',
            "user_id" => $user->id,
        ]);
        $subject = Subject::create(["name" => "English", "school_id" => $school->id]);
        $timeSlot = TimeSlot::create(["name" => "Period 2", "start_time" => "09:00:00", "end_time" => "09:45:00", "order" => 2, "school_id" => $school->id]);

        // Create a timetable entry for class and teacher
        $entry = Timetable::create([
            'school_id' => $school->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'time_slot_id' => $timeSlot->id,
            'day' => 'Tue',
            'term' => 'Term 1',
            'year' => date('Y'),
            'room' => 'B1'
        ]);

        // Hit class fragment (web route)
        $classResp = $this->get("/timetables/fragment/class/{$class->id}?term=Term%201&year=" . date('Y'));
        $classResp->assertStatus(200);
        $classResp->assertSee('<table', false);

        // Hit teacher fragment
        $teacherResp = $this->get("/timetables/fragment/teacher/{$teacher->id}?term=Term%201&year=" . date('Y'));
        $teacherResp->assertStatus(200);
        $teacherResp->assertSee('<table', false);
    }
}
