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

class TimetableMetaTest extends TestCase
{
    use RefreshDatabase;

    public function test_meta_endpoints_return_last_updated_ts_and_pdf_exists()
    {
        // create a school using factory (factory will ensure owner exists)
        $school = School::factory()->create();

        // create and authenticate a user so the auth-protected API can be called
        $user = User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test_meta_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'phone_number' => '0000000000',
            'school_id' => $school->id,
            'user_type' => 'admin'
        ]);
        $this->actingAs($user);

        // Create minimal related records
        $class = ClassModel::create(["name" => "Class A", "school_id" => $school->id]);
        $teacher = Teacher::create([
            "first_name" => "John",
            "last_name" => "Doe",
            "gender" => 'male',
            "phone" => '0700000002',
            "school_id" => $school->id,
            "class_id" => $class->id,
            "marital_status" => 'single',
            "date_of_birth" => '1985-01-01',
            "user_id" => $user->id,
        ]);
        $subject = Subject::create(["name" => "Math", "school_id" => $school->id]);
        $timeSlot = TimeSlot::create(["name" => "Period 1", "start_time" => "08:00:00", "end_time" => "08:45:00", "order" => 1, "school_id" => $school->id]);

        // Create a timetable entry
        $entry = Timetable::create([
            'school_id' => $school->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'time_slot_id' => $timeSlot->id,
            'day' => 'Mon',
            'term' => 'Term 1',
            'year' => date('Y'),
            'room' => 'A1'
        ]);

        // Hit class meta endpoint
        $classResp = $this->getJson("/api/timetables/class/{$class->id}/meta?term=Term%201&year=" . date('Y'));
        $classResp->assertStatus(200);
        $classResp->assertJsonStructure(['last_updated', 'last_updated_ts', 'pdf_exists']);

        // Hit teacher meta endpoint
        $teacherResp = $this->getJson("/api/timetables/teacher/{$teacher->id}/meta?term=Term%201&year=" . date('Y'));
        $teacherResp->assertStatus(200);
        $teacherResp->assertJsonStructure(['last_updated', 'last_updated_ts', 'pdf_exists']);
    }
}
