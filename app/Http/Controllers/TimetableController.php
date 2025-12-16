<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\Teacher;
use App\Models\TimeSlot;
use App\Models\Timetable;
use App\Models\ClassSubjectTeacher;
use App\Services\TimetablePdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Mail\TimetableMail;
use Illuminate\Support\Facades\Mail;

class TimetableController extends Controller
{
    // Student view by class
    public function classView(Request $request, $class_id)
    {
        $term = $request->query('term', 'Term 1');
        $year = $request->query('year', date('Y'));
        $cacheKey = "timetable_class_{$class_id}_{$term}_{$year}";

        $timetable = Cache::remember($cacheKey, 60*60, function () use ($class_id, $term, $year) {
            return Timetable::with(['subject','teacher','timeSlot'])
                ->where('class_id', $class_id)
                ->where('term', $term)
                ->where('year', $year)
                ->get()
                ->groupBy(['day','time_slot_id']);
        });

        $class = ClassModel::findOrFail($class_id);
        $timeSlots = TimeSlot::where('school_id', $class->school_id)->orderBy('order')->get();

        return view('timetables.student', compact('timetable','class','timeSlots','term','year'));
    }

    // Teacher view by teacher id
    public function teacherView(Request $request, $teacher_id)
    {
        $term = $request->query('term', 'Term 1');
        $year = $request->query('year', date('Y'));
        $cacheKey = "timetable_teacher_{$teacher_id}_{$term}_{$year}";

        $timetable = Cache::remember($cacheKey, 60*60, function () use ($teacher_id, $term, $year) {
            return Timetable::with(['subject','class','timeSlot'])
                ->where('teacher_id', $teacher_id)
                ->where('term', $term)
                ->where('year', $year)
                ->get()
                ->groupBy(['day','time_slot_id']);
        });

        $teacher = Teacher::findOrFail($teacher_id);
        $timeSlots = TimeSlot::where('school_id', $teacher->school_id)->orderBy('order')->get();

        return view('timetables.teacher', compact('timetable','teacher','timeSlots','term','year'));
    }

    // Admin builder
    public function adminIndex(Request $request)
    {
        $schoolId = $request->query('school_id', 1);
        $classes = ClassModel::where('school_id', $schoolId)->get();
        $teachers = Teacher::where('school_id', $schoolId)->get();
        $timeSlots = TimeSlot::where('school_id', $schoolId)->orderBy('order')->get();
        $subjects = ClassSubjectTeacher::where('school_id', $schoolId)->with(['subject','class','teacher'])->get();

        return view('timetables.admin', compact('classes','teachers','timeSlots','subjects'));
    }

    // Store a timetable entry with validations
    public function storeEntry(Request $request)
    {
        $data = $request->only(['school_id','class_id','subject_id','teacher_id','time_slot_id','day','room','term','year']);

        $validator = Validator::make($data, [
            'school_id'=>'required|integer',
            'class_id'=>'required|integer',
            'subject_id'=>'required|integer',
            'teacher_id'=>'required|integer',
            'time_slot_id'=>'required|integer',
            'day'=>'required|in:Mon,Tue,Wed,Thu,Fri',
            'term'=>'required|string',
            'year'=>'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()], 422);
        }

        // Conflict checks
        $conflictClass = Timetable::where('class_id',$data['class_id'])
            ->where('day',$data['day'])
            ->where('time_slot_id',$data['time_slot_id'])
            ->where('term',$data['term'])
            ->where('year',$data['year'])
            ->first();

        if ($conflictClass) {
            return response()->json(['error'=>'Class already has a subject in this slot'], 409);
        }

        $conflictTeacher = Timetable::where('teacher_id',$data['teacher_id'])
            ->where('day',$data['day'])
            ->where('time_slot_id',$data['time_slot_id'])
            ->where('term',$data['term'])
            ->where('year',$data['year'])
            ->first();

        if ($conflictTeacher) {
            return response()->json(['error'=>'Teacher already assigned to another class at this time'], 409);
        }

        if (!empty($data['room'])) {
            $conflictRoom = Timetable::where('room',$data['room'])
                ->where('day',$data['day'])
                ->where('time_slot_id',$data['time_slot_id'])
                ->where('term',$data['term'])
                ->where('year',$data['year'])
                ->first();
            if ($conflictRoom) {
                return response()->json(['error'=>'Room is already in use at this time'], 409);
            }
        }

        $entry = Timetable::create($data);

        // Clear relevant caches
        Cache::forget("timetable_class_{$data['class_id']}_{$data['term']}_{$data['year']}");
        Cache::forget("timetable_teacher_{$data['teacher_id']}_{$data['term']}_{$data['year']}");

        // Auto-generate and store PDFs for the affected class and teacher
        try {
            $pdfService = new TimetablePdfService();

            // Class PDF
            $classTimetable = Timetable::with(['subject','teacher','timeSlot'])
                ->where('class_id', $data['class_id'])
                ->where('term', $data['term'])
                ->where('year', $data['year'])
                ->get();
            $classMeta = ['role' => 'Class', 'name' => ClassModel::find($data['class_id'])->name ?? ''];
            $classPdf = $pdfService->generatePdf($classTimetable, $classMeta, $data['term'], $data['year']);
            $classFolder = "timetables/" . Str::slug($data['term']) . "/{$data['year']}";
            $classFilename = "class_{$data['class_id']}_{$data['term']}_{$data['year']}.pdf";
            Storage::put("{$classFolder}/{$classFilename}", $classPdf->output());

            // Teacher PDF
            $teacherTimetable = Timetable::with(['subject','class','timeSlot'])
                ->where('teacher_id', $data['teacher_id'])
                ->where('term', $data['term'])
                ->where('year', $data['year'])
                ->get();
            $teacher = Teacher::find($data['teacher_id']);
            $teacherMeta = ['role' => 'Teacher', 'name' => $teacher?->first_name . ' ' . $teacher?->last_name];
            $teacherPdf = $pdfService->generatePdf($teacherTimetable, $teacherMeta, $data['term'], $data['year']);
            $teacherFilename = "teacher_{$data['teacher_id']}_{$data['term']}_{$data['year']}.pdf";
            Storage::put("{$classFolder}/{$teacherFilename}", $teacherPdf->output());
        } catch (\Exception $e) {
            // Log the error but don't block the response
            \Illuminate\Support\Facades\Log::error('Timetable PDF generation failed: ' . $e->getMessage());
        }

        return response()->json(['success'=>true,'entry'=>$entry]);
    }

    // Generate or fetch cached PDF
    public function downloadPdf(Request $request)
    {
        $type = $request->query('type'); // class or teacher
        $id = $request->query('id');
        $term = $request->query('term', 'Term 1');
        $year = $request->query('year', date('Y'));

        if (!in_array($type, ['class','teacher'])) {
            abort(400,'Invalid type');
        }

        // consistent folder path for storage
        $folder = "timetables/" . Str::slug($term) . "/{$year}";
        $filename = "{$type}_{$id}_{$term}_{$year}.pdf";
        $path = "{$folder}/{$filename}";

        if (Storage::exists($path)) {
            return Storage::download($path, $filename);
        }

        $pdfService = new TimetablePdfService();
        if ($type==='class') {
            $class = ClassModel::findOrFail($id);
            $timetable = Timetable::with(['subject','teacher','timeSlot'])
                ->where('class_id',$id)->where('term',$term)->where('year',$year)->get();
            $meta = ['role'=>'Class','name'=>$class->name];
        } else {
            $teacher = Teacher::findOrFail($id);
            $timetable = Timetable::with(['subject','class','timeSlot'])
                ->where('teacher_id',$id)->where('term',$term)->where('year',$year)->get();
            $meta = ['role'=>'Teacher','name'=>$teacher->first_name.' '.$teacher->last_name];
        }

        $pdf = $pdfService->generatePdf($timetable, $meta, $term, $year);

        // store
        Storage::put($path, $pdf->output());

        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, $filename);
    }

    public function shareLink(Request $request)
    {
        $type = $request->query('type');
        $id = $request->query('id');
        $term = $request->query('term','Term 1');
        $year = $request->query('year',date('Y'));

        $url = url("/timetables/download?type={$type}&id={$id}&term=".urlencode($term)."&year={$year}");
        $text = urlencode("Here is the timetable: {$url}");
        $wa = "https://wa.me/?text={$text}";

        return redirect($wa);
    }

    public function emailTimetable(Request $request)
    {
        $request->validate([
            'type'=>'required|in:class,teacher',
            'id'=>'required|integer',
            'email'=>'required|email',
            'term'=>'required|string',
            'year'=>'required|string',
        ]);

        $type = $request->input('type');
        $id = $request->input('id');
        $term = $request->input('term');
        $year = $request->input('year');

        // ensure PDF exists
        $folder = "timetables/".Str::slug($term)."/{$year}";
        $filename = "{$type}_{$id}_{$term}_{$year}.pdf";
        $path = "{$folder}/{$filename}";

        if (!Storage::exists($path)) {
            // generate
            $pdfService = new TimetablePdfService();
            if ($type==='class') {
                $timetable = Timetable::with(['subject','teacher','timeSlot'])
                    ->where('class_id',$id)->where('term',$term)->where('year',$year)->get();
                $meta = ['role'=>'Class','name'=>ClassModel::find($id)->name];
            } else {
                $timetable = Timetable::with(['subject','class','timeSlot'])
                    ->where('teacher_id',$id)->where('term',$term)->where('year',$year)->get();
                $t = Teacher::find($id);
                $meta = ['role'=>'Teacher','name'=>$t?->first_name.' '.$t?->last_name];
            }
            $pdf = $pdfService->generatePdf($timetable, $meta, $term, $year);
            Storage::put($path, $pdf->output());
        }

        Mail::to($request->input('email'))->send(new TimetableMail($path));

        return response()->json(['success'=>true]);
    }

    // API meta endpoint for a class: returns last_updated and pdf_exists
    public function metaClass(Request $request, $id)
    {
        $term = $request->query('term', 'Term 1');
        $year = $request->query('year', date('Y'));

        $last = Timetable::where('class_id', $id)
            ->where('term', $term)
            ->where('year', $year)
            ->orderBy('updated_at', 'desc')
            ->first();

        $folder = "timetables/" . Str::slug($term) . "/{$year}";
        $filename = "class_{$id}_{$term}_{$year}.pdf";
        $path = "{$folder}/{$filename}";

        return response()->json([
            'last_updated' => $last?->updated_at?->toDateTimeString(),
            'last_updated_ts' => $last?->updated_at?->getTimestamp() ?? 0,
            'pdf_exists' => Storage::exists($path),
        ]);
    }

    // API meta endpoint for a teacher
    public function metaTeacher(Request $request, $id)
    {
        $term = $request->query('term', 'Term 1');
        $year = $request->query('year', date('Y'));

        $last = Timetable::where('teacher_id', $id)
            ->where('term', $term)
            ->where('year', $year)
            ->orderBy('updated_at', 'desc')
            ->first();

        $folder = "timetables/" . Str::slug($term) . "/{$year}";
        $filename = "teacher_{$id}_{$term}_{$year}.pdf";
        $path = "{$folder}/{$filename}";

        return response()->json([
            'last_updated' => $last?->updated_at?->toDateTimeString(),
            'last_updated_ts' => $last?->updated_at?->getTimestamp() ?? 0,
            'pdf_exists' => Storage::exists($path),
        ]);
    }

    // Return HTML fragment (table) for a class timetable (used for AJAX refresh)
    public function fragmentClass(Request $request, $class_id)
    {
        $term = $request->query('term', 'Term 1');
        $year = $request->query('year', date('Y'));

        $timetable = Timetable::with(['subject','teacher','timeSlot'])
            ->where('class_id', $class_id)
            ->where('term', $term)
            ->where('year', $year)
            ->get()
            ->groupBy(['day','time_slot_id']);

        $class = ClassModel::findOrFail($class_id);
        $timeSlots = TimeSlot::where('school_id', $class->school_id)->orderBy('order')->get();

        return view('timetables._table', compact('timetable','timeSlots'));
    }

    // Return HTML fragment (table) for a teacher timetable (used for AJAX refresh)
    public function fragmentTeacher(Request $request, $teacher_id)
    {
        $term = $request->query('term', 'Term 1');
        $year = $request->query('year', date('Y'));

        $timetable = Timetable::with(['subject','class','timeSlot'])
            ->where('teacher_id', $teacher_id)
            ->where('term', $term)
            ->where('year', $year)
            ->get()
            ->groupBy(['day','time_slot_id']);

        $teacher = Teacher::findOrFail($teacher_id);
        $timeSlots = TimeSlot::where('school_id', $teacher->school_id)->orderBy('order')->get();

        return view('timetables._table', compact('timetable','timeSlots'));
    }
}
