<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ClassModel;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    /**
     * Attendance screen: heading for the teacher's class, a "Take New
     * Attendance" action, and a summary of previous registers below.
     */
    public function index()
    {
        $class = $this->myClass();

        $recent = collect();

        if ($class) {
            $recent = Attendance::where('class_id', $class->id)
                ->orderByDesc('date')
                ->limit(2000)
                ->get(['date', 'status'])
                ->groupBy('date')
                ->take(10)
                ->map(function ($day) {
                    return [
                        'present' => $day->where('status', 'present')->count(),
                        'absent'  => $day->where('status', 'absent')->count(),
                    ];
                });
        }

        return view('attendance.index', compact('class', 'recent'));
    }

    /**
     * Date selector (defaults to today). If a register already exists for the
     * chosen date it is loaded and can be edited.
     */
    public function register(Request $request)
    {
        $class = $this->myClass();

        $date = $request->input('date', now()->toDateString());

        $pupils = collect();
        $existing = [];
        $savedForDate = false;

        if ($class && $this->validDate($date)) {
            $pupils = $class->pupils()
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get();

            $existing = Attendance::where('class_id', $class->id)
                ->where('date', $date)
                ->pluck('status', 'pupil_id')
                ->toArray();

            $savedForDate = !empty($existing);
        }

        return view('attendance.register', compact('class', 'date', 'pupils', 'existing', 'savedForDate'));
    }

    /**
     * Save (or update) the register for the class teacher's class on one date.
     */
    public function store(Request $request)
    {
        $class = $this->myClass();

        if (!$class) {
            return back()->with('error', 'You are not set as the Class Teacher of any class yet.');
        }

        $data = $request->validate([
            'date'   => 'required|date',
            'status' => 'required|array',
            'status.*' => 'in:present,absent',
        ]);

        if (!$this->validDate($data['date'])) {
            return back()->with('error', 'Attendance can only be taken for today or an earlier date.');
        }

        $teacher = $this->currentTeacher();

        // Source of truth is the current class roster, not what was posted.
        $roster = $class->pupils()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $rows = [];
        foreach ($roster as $pupil) {
            $rows[] = [
                'school_id' => $class->school_id,
                'class_id'  => $class->id,
                'pupil_id'  => $pupil->id,
                'date'      => $data['date'],
                'status'    => $data['status'][$pupil->id] ?? 'absent',
                'teacher_id'=> $teacher->id,
                'created_at'=> now(),
                'updated_at'=> now(),
            ];
        }

        Attendance::upsert($rows, ['pupil_id', 'date'], ['class_id', 'status', 'teacher_id', 'updated_at']);

        $present = collect($rows)->where('status', 'present')->count();

        return redirect()
            ->route('attendance.register', ['date' => $data['date']])
            ->with('success', "Register saved for {$data['date']} — {$present} of {$roster->count()} pupils present.");
    }

    /**
     * Overall attendance summary per pupil over a date range (a term or the year).
     */
    public function summary(Request $request)
    {
        $class = $this->myClass();

        $from = $request->input('from', now()->startOfYear()->toDateString());
        $to   = $request->input('to', now()->toDateString());

        $pupils = collect();
        $stats = collect();
        $daysRecorded = 0;

        if ($class && $from <= $to) {
            $pupils = $class->pupils()->orderBy('first_name')->orderBy('last_name')->get();

            $stats = Attendance::where('class_id', $class->id)
                ->whereBetween('date', [$from, $to])
                ->selectRaw("pupil_id, COUNT(*) as total,
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
                    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent")
                ->groupBy('pupil_id')
                ->get()
                ->keyBy('pupil_id');

            $daysRecorded = Attendance::where('class_id', $class->id)
                ->whereBetween('date', [$from, $to])
                ->distinct()
                ->count('date');
        }

        return view('attendance.stats', compact('class', 'from', 'to', 'pupils', 'stats', 'daysRecorded'));
    }

    /**
     * Attendance broken down day by day (who was present / absent each day).
     */
    public function days(Request $request)
    {
        $class = $this->myClass();

        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to   = $request->input('to', now()->toDateString());

        $days = collect();
        $rosterCount = 0;

        if ($class && $from <= $to) {
            $rosterCount = $class->pupils()->count();

            $days = Attendance::where('class_id', $class->id)
                ->whereBetween('date', [$from, $to])
                ->with('pupil')
                ->orderByDesc('date')
                ->get()
                ->groupBy('date');
        }

        return view('attendance.days', compact('class', 'from', 'to', 'days', 'rosterCount'));
    }

    /**
     * The class this teacher is the Class Teacher of.
     * Falls back to a single legacy pivot assignment for existing data.
     */
    private function myClass(): ?ClassModel
    {
        $teacher = $this->currentTeacher();

        if ($teacher->class_id) {
            return $teacher->class;
        }

        $assigned = $teacher->classes()->limit(2)->get();
        if ($assigned->count() === 1) {
            return $assigned->first();
        }

        return null;
    }

    private function currentTeacher(): Teacher
    {
        return Teacher::where('user_id', Auth::id())->firstOrFail();
    }

    private function validDate(string $date): bool
    {
        return $date <= now()->format('Y-m-d');
    }
}
