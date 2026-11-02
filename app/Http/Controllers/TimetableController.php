<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\School;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TimetableEntry;
use App\Services\TimetableGenerator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

/**
 * A timetable is one weekly template per term. Generate it once, the school
 * uses it for the whole term.
 *
 * Admin  : pick a term, generate, edit any cell (conflicts allowed), publish.
 * Teacher: read-only week, today's periods on the dashboard, print / PDF.
 */
class TimetableController extends Controller
{
    /* ------------------------------------------------------------------ admin */

    public function index(Request $request)
    {
        $school = $this->adminSchool();

        if (! $school) {
            return $this->notAllowed();
        }

        $term = $this->term($request);

        $classes = ClassModel::where('school_id', $school->id)->orderBy('name')->get();

        $counts = TimetableEntry::where('school_id', $school->id)
            ->where('term', $term)
            ->selectRaw('class_id, COUNT(*) as cells, COUNT(published_at) as published')
            ->groupBy('class_id')
            ->get()
            ->keyBy('class_id');

        return view('timetable.index', [
            'school'  => $school,
            'term'    => $term,
            'classes' => $classes,
            'counts'  => $counts,
            'terms'   => TimetableEntry::TERMS,
            'days'    => $this->days($school),
            'periods' => $this->periods($school),
            'subjects' => Subject::where('school_id', $school->id)->count(),
        ]);
    }

    public function settings(Request $request)
    {
        $school = $this->adminSchool();

        if (! $school) {
            return $this->notAllowed();
        }

        $data = $request->validate([
            'days_per_week'   => 'required|integer|min:1|max:7',
            'periods_per_day' => 'required|integer|min:1|max:12',
        ]);

        $school->update($data);

        return back()->with('success', "Timetable grid set to {$data['days_per_week']} days × {$data['periods_per_day']} periods.");
    }

    public function generate(Request $request)
    {
        $school = $this->adminSchool();

        if (! $school) {
            return $this->notAllowed();
        }

        $data = $request->validate([
            'term'     => 'required|string|max:20',
            'class_id' => 'nullable|integer',
        ]);

        try {
            $cells = TimetableGenerator::generate(
                $school,
                $data['term'],
                isset($data['class_id']) ? (int) $data['class_id'] : null
            );
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Generated {$cells} period(s) for {$data['term']}. Review it, then confirm to publish.");
    }

    public function clear(Request $request)
    {
        $school = $this->adminSchool();

        if (! $school) {
            return $this->notAllowed();
        }

        $data = $request->validate([
            'term'     => 'required|string|max:20',
            'class_id' => 'nullable|integer',
        ]);

        $deleted = TimetableEntry::where('school_id', $school->id)
            ->where('term', $data['term'])
            ->when($data['class_id'] ?? null, fn ($query, $classId) => $query->where('class_id', $classId))
            ->delete();

        return back()->with('success', "Cleared {$deleted} period(s) for {$data['term']}.");
    }

    public function publish(Request $request)
    {
        $school = $this->adminSchool();

        if (! $school) {
            return $this->notAllowed();
        }

        $data = $request->validate([
            'term'     => 'required|string|max:20',
            'class_id' => 'nullable|integer',
            'action'   => 'required|in:publish,unpublish',
        ]);

        $value = $data['action'] === 'publish' ? now() : null;

        $affected = TimetableEntry::where('school_id', $school->id)
            ->where('term', $data['term'])
            ->when($data['class_id'] ?? null, fn ($query, $classId) => $query->where('class_id', $classId))
            ->update(['published_at' => $value]);

        return back()->with('success', $value
            ? "Timetable published ({$affected} periods) — teachers can see it now."
            : "Timetable unpublished ({$affected} periods) — hidden from teachers.");
    }

    public function grid(Request $request, ClassModel $class)
    {
        $school = $this->adminSchool();

        if (! $school || (int) $class->school_id !== (int) $school->id) {
            return $this->notAllowed();
        }

        $term = $this->term($request);

        $entries = TimetableEntry::where('class_id', $class->id)
            ->where('term', $term)
            ->get()
            ->keyBy(fn ($entry) => $entry->day . '-' . $entry->period);

        return view('timetable.grid', [
            'school'   => $school,
            'class'    => $class,
            'term'     => $term,
            'terms'    => TimetableEntry::TERMS,
            'days'     => $this->days($school),
            'periods'  => $this->periods($school),
            'entries'  => $entries,
            'subjects' => Subject::where('school_id', $school->id)->orderBy('name')->get(),
            'teachers' => Teacher::where('school_id', $school->id)->orderBy('last_name')->get(),
            'conflicts' => $this->teacherConflicts($school->id, $term),
            'published' => $entries->whereNotNull('published_at')->isNotEmpty(),
        ]);
    }

    public function save(Request $request, ClassModel $class)
    {
        $school = $this->adminSchool();

        if (! $school || (int) $class->school_id !== (int) $school->id) {
            return $this->notAllowed();
        }

        $data = $request->validate([
            'term' => 'required|string|max:20',
            'cells' => 'array',
        ]);

        $term = $data['term'];

        // Only this school's subjects and teachers may be referenced.
        $subjectIds = Subject::where('school_id', $school->id)->pluck('id')->all();
        $teacherIds = Teacher::where('school_id', $school->id)->pluck('id')->all();
        $publishedAt = TimetableEntry::where('school_id', $school->id)
            ->where('term', $term)
            ->whereNotNull('published_at')
            ->value('published_at');

        $saved = 0;

        foreach ($request->input('cells', []) as $day => $periods) {
            $day = (int) $day;

            if ($day < 1 || $day > 7) {
                continue;
            }

            foreach ((array) $periods as $period => $cell) {
                $period = (int) $period;

                if ($period < 1 || $period > 12) {
                    continue;
                }

                $subjectId = (int) ($cell['subject_id'] ?? 0) ?: null;
                $teacherId = (int) ($cell['teacher_id'] ?? 0) ?: null;

                $subjectId = in_array($subjectId, $subjectIds, true) ? $subjectId : null;
                $teacherId = in_array($teacherId, $teacherIds, true) ? $teacherId : null;

                // No subject means an empty cell: clear the teacher with it.
                if ($subjectId === null) {
                    $teacherId = null;
                }

                TimetableEntry::updateOrCreate(
                    [
                        'class_id' => $class->id,
                        'term'     => $term,
                        'day'      => $day,
                        'period'   => $period,
                    ],
                    [
                        'school_id'    => $school->id,
                        'subject_id'   => $subjectId,
                        'teacher_id'   => $teacherId,
                        'published_at' => $publishedAt,
                    ]
                );

                $saved++;
            }
        }

        return back()->with('success', "Saved {$saved} cell(s) for " . ucwords($term) . '.');
    }

    /* ---------------------------------------------------------------- teacher */

    public function mine()
    {
        $teacher = $this->currentTeacher();

        if (! $teacher) {
            return $this->notLinked();
        }

        $week = $this->teacherWeek($teacher);

        return view('timetable.mine', $week + [
            'teacher'   => $teacher,
            'myClasses' => $this->visibleClasses(),
        ]);
    }

    public function printView()
    {
        $teacher = $this->currentTeacher();

        if (! $teacher) {
            return $this->notLinked();
        }

        return view('timetable.print', $this->printData($teacher, false));
    }

    public function pdf()
    {
        $teacher = $this->currentTeacher();

        if (! $teacher) {
            return $this->notLinked();
        }

        // DomPDF needs a filesystem path for the emblem; the browser needs a URL.
        $data = $this->printData($teacher, true);

        return Pdf::loadView('timetable.print', $data)
            ->setPaper('a4', 'landscape')
            ->download('timetable_' . str_replace(' ', '_', $teacher->first_name . '_' . $teacher->last_name) . '.pdf');
    }

    /**
     * Shared data for the printable / downloadable timetable: the week plus the
     * school letterhead the report forms already use.
     */
    private function printData(Teacher $teacher, bool $forPdf): array
    {
        $week   = $this->teacherWeek($teacher);
        $school = $week['school'] ?? null;

        return $week + [
            'teacher'   => $teacher,
            'school'    => $school,
            'heading'   => "Teacher's Timetable",
            'subtitle'  => trim($teacher->first_name . ' ' . $teacher->last_name),
            'logoPath'  => ($forPdf && $school && $school->photo)
                ? public_path('storage/' . $school->photo)
                : null,
        ];
    }

    /* ------------------------------------------------------- read-only views */

    /** Read-only list of the classes this user may look at. */
    public function classes()
    {
        $classes = $this->visibleClasses();

        return view('timetable.classes', [
            'classes' => $classes,
            'term'    => $this->currentTerm((int) Auth::user()->school_id),
        ]);
    }

    /** A whole class's published week, read-only. */
    public function classView(Request $request, ClassModel $class)
    {
        if (! $this->canViewClass($class)) {
            return $this->notAllowed();
        }

        $term = $this->currentTerm((int) $class->school_id, (string) $request->input('term', ''));

        $entries = TimetableEntry::with(['subject', 'teacher'])
            ->where('class_id', $class->id)
            ->where('term', $term)
            ->when(! $this->isAdmin(), fn ($query) => $query->whereNotNull('published_at'))
            ->get();

        $cells = [];
        foreach ($entries as $entry) {
            $cells[$entry->day][$entry->period] = $entry;
        }

        $school = School::find($class->school_id);

        return view('timetable.class-view', [
            'class'     => $class,
            'school'    => $school,
            'term'      => $term,
            'days'      => $this->days($school),
            'periods'   => $this->periods($school),
            'cells'     => $cells,
            'published' => $entries->whereNotNull('published_at')->isNotEmpty(),
        ]);
    }

    public function classPrint(Request $request, ClassModel $class)
    {
        if (! $this->canViewClass($class)) {
            return $this->notAllowed();
        }

        return view('timetable.print', $this->classPrintData($class, (string) $request->input('term', ''), false));
    }

    public function classPdf(Request $request, ClassModel $class)
    {
        if (! $this->canViewClass($class)) {
            return $this->notAllowed();
        }

        $data = $this->classPrintData($class, (string) $request->input('term', ''), true);

        return Pdf::loadView('timetable.print', $data)
            ->setPaper('a4', 'landscape')
            ->download('timetable_' . preg_replace('/[^A-Za-z0-9]+/', '_', $class->name) . '.pdf');
    }

    /* ---------------------------------------------------------------- helpers */

    private function isAdmin(): bool
    {
        return Auth::user()->user_type === 'admin';
    }

    /**
     * Who may read a class's timetable: the school's admin and secretary, plus
     * any teacher who is the class teacher, is linked to the class, or teaches
     * a period in it. Everyone is scoped to their own school.
     */
    private function canViewClass(ClassModel $class): bool
    {
        $user = Auth::user();

        if (! $user || (int) $class->school_id !== (int) $user->school_id) {
            return false;
        }

        if (in_array($user->user_type, ['admin', 'secretary'], true)) {
            return true;
        }

        $teacher = $this->currentTeacher();

        if (! $teacher) {
            return false;
        }

        if ((int) $teacher->class_id === (int) $class->id) {
            return true;
        }

        if (\DB::table('class_teacher')
            ->where('class_id', $class->id)
            ->where('teacher_id', $teacher->id)
            ->exists()) {
            return true;
        }

        return TimetableEntry::where('class_id', $class->id)
            ->where('teacher_id', $teacher->id)
            ->exists();
    }

    /** Classes this user may open, for the class list and the teacher's own page. */
    private function visibleClasses()
    {
        $user     = Auth::user();
        $classes  = ClassModel::where('school_id', $user->school_id)->orderBy('name')->get();

        if (in_array($user->user_type, ['admin', 'secretary'], true)) {
            return $classes;
        }

        $teacher = $this->currentTeacher();

        if (! $teacher) {
            return collect();
        }

        $linked = \DB::table('class_teacher')->where('teacher_id', $teacher->id)->pluck('class_id')->all();
        $taught = TimetableEntry::where('teacher_id', $teacher->id)->distinct()->pluck('class_id')->all();

        return $classes->filter(fn ($class) => (int) $teacher->class_id === (int) $class->id
            || in_array($class->id, $linked)
            || in_array($class->id, $taught))->values();
    }

    /** The term to show: an explicit one, else the latest published, else Term 1. */
    private function currentTerm(int $schoolId, string $requested = ''): string
    {
        if (in_array($requested, TimetableEntry::TERMS, true)) {
            return $requested;
        }

        $latest = TimetableEntry::where('school_id', $schoolId)
            ->whereNotNull('published_at')
            ->orderByDesc('published_at')
            ->value('term');

        return $latest ?: TimetableEntry::TERMS[0];
    }

    private function classPrintData(ClassModel $class, string $requestedTerm, bool $forPdf): array
    {
        $school = School::find($class->school_id);
        $term   = $this->currentTerm((int) $class->school_id, $requestedTerm);

        $entries = TimetableEntry::with(['subject', 'teacher'])
            ->where('class_id', $class->id)
            ->where('term', $term)
            ->when(! $this->isAdmin(), fn ($query) => $query->whereNotNull('published_at'))
            ->get();

        $cells = [];
        foreach ($entries as $entry) {
            $cells[$entry->day][$entry->period] = $entry;
        }

        return [
            'heading'  => 'Class Timetable',
            'subtitle' => $class->name,
            'school'   => $school,
            'term'     => $term,
            'days'     => $this->days($school),
            'periods'  => $this->periods($school),
            'cells'    => $cells,
            'extra'    => [],
            'logoPath' => ($forPdf && $school && $school->photo)
                ? public_path('storage/' . $school->photo)
                : null,
        ];
    }

    private function adminSchool(): ?School
    {
        if (Auth::user()->user_type !== 'admin') {
            return null;
        }

        return School::find(Auth::user()->school_id);
    }

    private function currentTeacher(): ?Teacher
    {
        return Teacher::where('user_id', Auth::id())->first();
    }

    private function notAllowed()
    {
        return redirect()->route('dashboard')
            ->with('error', 'You are not authorized to view that timetable.');
    }

    private function notLinked()
    {
        return redirect()->route('dashboard')
            ->with('error', 'Your account is not linked to a teacher record, so there is no timetable to show. Ask the admin to link you.');
    }

    private function term(Request $request): string
    {
        $term = (string) $request->input('term', '');

        return in_array($term, TimetableEntry::TERMS, true) ? $term : TimetableEntry::TERMS[0];
    }

    private function days(School $school): int
    {
        return max(1, min(7, (int) ($school->days_per_week ?: 5)));
    }

    private function periods(School $school): int
    {
        return max(1, min(12, (int) ($school->periods_per_day ?: 8)));
    }

    /** Slots where the same teacher is booked twice — highlighted, never blocked. */
    private function teacherConflicts(int $schoolId, string $term): array
    {
        $keys = TimetableEntry::where('school_id', $schoolId)
            ->where('term', $term)
            ->whereNotNull('teacher_id')
            ->get(['teacher_id', 'day', 'period'])
            ->groupBy(fn ($row) => $row->teacher_id . '-' . $row->day . '-' . $row->period)
            ->filter(fn ($group) => $group->count() > 1)
            ->keys();

        return array_flip($keys->all());
    }

    /** The published week for one teacher, plus the term it belongs to. */
    private function teacherWeek(Teacher $teacher): array
    {
        $latest = TimetableEntry::where('school_id', $teacher->school_id)
            ->whereNotNull('published_at')
            ->orderByDesc('published_at')
            ->first();

        if (! $latest) {
            return [
                'term'    => null,
                'days'    => 5,
                'periods' => 8,
                'entries' => collect(),
                'cells'   => [],
                'extra'   => [],
                'school'  => School::find($teacher->school_id),
            ];
        }

        $school = School::find($teacher->school_id);

        $entries = TimetableEntry::with(['subject', 'class'])
            ->where('teacher_id', $teacher->id)
            ->where('term', $latest->term)
            ->whereNotNull('published_at')
            ->orderBy('day')
            ->orderBy('period')
            ->get();

        // A double-booked teacher has two classes in one slot. Keep the first as
        // the cell's entry and list the rest, so nothing silently disappears.
        $cells = [];
        $extra = [];

        foreach ($entries as $entry) {
            if (isset($cells[$entry->day][$entry->period])) {
                $extra[$entry->day][$entry->period][] = $entry->class->name ?? '';

                continue;
            }

            $cells[$entry->day][$entry->period] = $entry;
        }

        return [
            'term'    => $latest->term,
            'days'    => $this->days($school),
            'periods' => $this->periods($school),
            'entries' => $entries,
            'cells'   => $cells,
            'extra'   => $extra,
            'school'  => $school,
        ];
    }
}
