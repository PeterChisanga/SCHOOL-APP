<?php

namespace App\Services;

use App\Models\ClassModel;
use App\Models\School;
use App\Models\Subject;
use App\Models\TimetableEntry;
use RuntimeException;

/**
 * Fills one week per class for a term.
 *
 * Deliberately dumb and deterministic: cells are filled round-robin over the
 * school's subjects, so every subject appears either floor(40/N) or
 * ceil(40/N) times (40 cells ÷ N subjects) and all subjects are used.
 * Each class's week is the same balanced sequence rotated by a stride that is
 * coprime with the number of slots, otherwise every class in the school would
 * get the identical week and every teacher would be double-booked everywhere.
 * The rotation is a bijection, so the per-subject balance is untouched.
 * The teacher comes from the subject. Rerunning produces the identical week,
 * so nothing is random to debug.
 *
 * The admin can then edit any single cell by hand — conflicts included.
 */
class TimetableGenerator
{
    /** Coprime with any realistic slot count (5–84), so the rotation is a bijection. */
    private const CLASS_STRIDE = 7;

    /**
     * @return int number of cells written
     */
    public static function generate(School $school, string $term, ?int $classId = null): int
    {
        $subjects = Subject::where('school_id', $school->id)->orderBy('name')->get();

        if ($subjects->isEmpty()) {
            throw new RuntimeException('Add at least one subject before generating a timetable.');
        }

        $classes = ClassModel::where('school_id', $school->id)
            ->when($classId, fn ($query) => $query->where('id', $classId))
            ->orderBy('name')
            ->get();

        if ($classes->isEmpty()) {
            throw new RuntimeException('Add at least one class before generating a timetable.');
        }

        $days    = max(1, min(7, (int) ($school->days_per_week ?: 5)));
        $periods = max(1, min(12, (int) ($school->periods_per_day ?: 8)));

        $scope = TimetableEntry::where('school_id', $school->id)
            ->where('term', $term)
            ->when($classId, fn ($query) => $query->where('class_id', $classId));

        // Regenerating must not silently take a live timetable offline.
        $wasPublished = (clone $scope)->whereNotNull('published_at')->exists();

        $scope->delete();

        $now   = now();
        $count = $subjects->count();
        $rows  = [];

        foreach ($classes as $classIndex => $class) {
            for ($day = 1; $day <= $days; $day++) {
                for ($period = 1; $period <= $periods; $period++) {
                    // Round-robin over a rotation of the slot list: rotating by
                    // a stride coprime with the slot count moves each class's
                    // week without changing how often each subject appears.
                    $slots = $days * $periods;
                    $index = ((($period - 1) * $days + ($day - 1)) + $classIndex * self::CLASS_STRIDE) % $slots;

                    $subject = $subjects[$index % $count];

                    $rows[] = [
                        'school_id'  => $school->id,
                        'class_id'   => $class->id,
                        'term'       => $term,
                        'day'        => $day,
                        'period'     => $period,
                        'subject_id' => $subject->id,
                        'teacher_id' => $subject->teacher_id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            TimetableEntry::insert($chunk);
        }

        if ($wasPublished) {
            TimetableEntry::where('school_id', $school->id)
                ->where('term', $term)
                ->when($classId, fn ($query) => $query->where('class_id', $classId))
                ->update(['published_at' => $now]);
        }

        return count($rows);
    }
}
