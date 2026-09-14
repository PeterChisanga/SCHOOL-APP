<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ClassModel;
use App\Models\Pupil;
use App\Models\School;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PDF;

class AdminAttendanceController extends Controller
{
    /**
     * Oversight: every class in the school for one day, with its register status.
     */
    public function index(Request $request)
    {
        $schoolId = Auth::user()->school_id;
        $date = $request->input('date', now()->toDateString());

        $classes = ClassModel::where('school_id', $schoolId)->orderBy('name')->get();

        $rosterCounts = Pupil::where('school_id', $schoolId)
            ->selectRaw('class_id, COUNT(*) as total')
            ->groupBy('class_id')
            ->pluck('total', 'class_id');

        $recorded = Attendance::where('school_id', $schoolId)
            ->where('date', $date)
            ->selectRaw("class_id, COUNT(*) as recorded,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent")
            ->groupBy('class_id')
            ->get()
            ->keyBy('class_id');

        $classTeachers = Teacher::where('school_id', $schoolId)
            ->whereNotNull('class_id')
            ->get()
            ->keyBy('class_id');

        $rows = $classes->map(function ($class) use ($rosterCounts, $recorded, $classTeachers) {
            $rec = $recorded->get($class->id);
            return [
                'class'     => $class,
                'teacher'   => $classTeachers->get($class->id),
                'enrolled'  => (int) ($rosterCounts->get($class->id) ?? 0),
                'recorded'  => (int) ($rec->recorded ?? 0),
                'present'   => (int) ($rec->present ?? 0),
                'absent'    => (int) ($rec->absent ?? 0),
                'taken'     => $rec !== null,
            ];
        });

        $summary = [
            'classes' => $classes->count(),
            'taken'   => $rows->where('taken', true)->count(),
            'present' => $rows->sum('present'),
            'absent'  => $rows->sum('absent'),
        ];

        return view('admin.attendance.index', compact('date', 'rows', 'summary'));
    }

    /**
     * Read-only daily register for one class.
     */
    public function show(Request $request, ClassModel $class)
    {
        if ($class->school_id !== Auth::user()->school_id) {
            return redirect()->route('admin.attendance.index')->with('error', 'You are not authorized to view this class.');
        }

        $date = $request->input('date', now()->toDateString());

        $pupils = $class->pupils()->orderBy('first_name')->orderBy('last_name')->get();
        $existing = Attendance::where('class_id', $class->id)
            ->where('date', $date)
            ->pluck('status', 'pupil_id')
            ->toArray();
        $teacher = Teacher::where('class_id', $class->id)->first();

        return view('admin.attendance.show', compact('class', 'date', 'pupils', 'existing', 'teacher'));
    }

    /**
     * Printable daily attendance sheet (with school details) for any class.
     */
    public function pdf(Request $request, ClassModel $class)
    {
        if ($class->school_id !== Auth::user()->school_id) {
            return redirect()->route('admin.attendance.index')->with('error', 'You are not authorized to export this class.');
        }

        $date = $request->input('date', now()->toDateString());
        $school = School::find(Auth::user()->school_id);
        $pupils = $class->pupils()->orderBy('first_name')->orderBy('last_name')->get();
        $existing = Attendance::where('class_id', $class->id)
            ->where('date', $date)
            ->pluck('status', 'pupil_id')
            ->toArray();
        $teacher = Teacher::where('class_id', $class->id)->first();

        $pdf = PDF::loadView('attendance.pdf', compact('school', 'class', 'date', 'pupils', 'existing', 'teacher'));

        return $pdf->download('attendance_' . str_replace(' ', '_', $class->name) . '_' . $date . '.pdf');
    }
}
