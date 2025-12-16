<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School;
use App\Models\ClassModel;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\TimeSlot;
use App\Models\ClassSubjectTeacher;
use App\Models\Timetable;
use App\Models\User;

class TimetableSeeder extends Seeder
{
    public function run(): void
    {
        // ensure an owner user exists for the school foreign key
        $owner = User::firstOrCreate([
            'email' => 'owner@example.com'
        ], [
            'first_name' => 'Owner',
            'last_name' => 'User',
            'phone_number' => '0000000009',
            'password' => bcrypt('password'),
            'user_type' => 'admin'
        ]);

        // create or get school (include owner_id)
        $school = School::first() ?? School::create([
            'name'=>'Sample School',
            'address'=>'123 Example St',
            'email'=>'info@example.school',
            'motto'=>'Learning for life',
            'phone'=>'0000000000',
            'owner_id' => $owner->id,
        ]);

        // ensure a user exists for teacher foreign key (use proper columns)
        $user = User::firstOrCreate([
            'email' => 'seed@example.com'
        ], [
            'first_name' => 'Seeder',
            'last_name' => 'User',
            'phone_number' => '0000000008',
            'password' => bcrypt('password'),
            'user_type' => 'admin',
            'school_id' => $school->id,
        ]);

        // classes
        $classes = [];
        for ($i=1;$i<=3;$i++) {
            $classes[] = ClassModel::firstOrCreate(['name'=>'Grade '.$i,'school_id'=>$school->id]);
        }

        // teachers
        $teachers = [];
        // ensure teachers reference an actual class (some migrations make class_id non-nullable)
        $defaultClassId = $classes[0]->id;
        $teachers[] = Teacher::updateOrCreate(['email'=>'t1@example.com'], [
            'first_name'=>'Alice','middle_name'=>null,'last_name'=>'Smith','gender'=>'F','phone'=>'0711000001','address'=>'','marital_status'=>'Single','date_of_birth'=>'1990-01-01','admission_date'=>now(),'qualification'=>'BEd','salary'=>'','school_id'=>$school->id,'class_id'=>$defaultClassId,'user_id'=>$user->id
        ]);
        $teachers[] = Teacher::updateOrCreate(['email'=>'t2@example.com'], [
            'first_name'=>'Bob','middle_name'=>null,'last_name'=>'Jones','gender'=>'M','phone'=>'0711000002','address'=>'','marital_status'=>'Single','date_of_birth'=>'1988-01-01','admission_date'=>now(),'qualification'=>'BEd','salary'=>'','school_id'=>$school->id,'class_id'=>$defaultClassId,'user_id'=>$user->id
        ]);

        // subjects
        $subjectNames = ['Mathematics','English','Science'];
        $subjects = [];
        foreach ($subjectNames as $name) {
            $subjects[$name] = Subject::firstOrCreate(['name'=>$name,'school_id'=>$school->id]);
        }

        // timeslots (3 slots) to keep seeding small
        $timeSlots = [];
        $times = [ ['Period 1','08:00:00','08:40:00',1], ['Period 2','08:50:00','09:30:00',2], ['Period 3','09:40:00','10:20:00',3] ];
        foreach ($times as $t) {
            $timeSlots[] = TimeSlot::firstOrCreate(['name'=>$t[0],'school_id'=>$school->id], [ 'start_time'=>$t[1],'end_time'=>$t[2],'order'=>$t[3] ]);
        }

        // class_subject_teacher assignments: assign one subject per class and teachers in round-robin
        foreach ($classes as $idx => $c) {
            $sub = array_values($subjects)[$idx % count($subjects)];
            $teacher = $teachers[$idx % count($teachers)];

            ClassSubjectTeacher::firstOrCreate([
                'class_id'=>$c->id,'subject_id'=>$sub->id,'school_id'=>$school->id
            ],['teacher_id'=>$teacher->id]);
        }

        // timetable entries: for each class, assign entries on Mon-Fri but use different slot index per class to avoid teacher conflicts
        $days = ['Mon','Tue','Wed','Thu','Fri'];
        foreach ($classes as $cidx => $c) {
            $slot = $timeSlots[$cidx % count($timeSlots)];
            foreach ($days as $day) {
                // assign a subject and teacher from ClassSubjectTeacher (if exists)
                $cst = ClassSubjectTeacher::where('class_id',$c->id)->first();
                if (!$cst) continue;

                // create entry if not exists
                Timetable::firstOrCreate([
                    'school_id'=>$school->id,
                    'class_id'=>$c->id,
                    'subject_id'=>$cst->subject_id,
                    'teacher_id'=>$cst->teacher_id,
                    'time_slot_id'=>$slot->id,
                    'day'=>$day,
                    'term'=>'Term 1',
                    'year'=>date('Y')
                ],['room'=>'A1']);
            }
        }
    }
}
