<?php

namespace App\Services;

use App\Models\ExamResult;
use Illuminate\Support\Facades\DB;

/**
 * Exam results are never destroyed.
 *
 * Anything removed from `exam_results` — by the dedupe migration, by "delete
 * this term", by "delete this result", or by clearing both halves of a mark —
 * is copied into `exam_results_archive` first. The archive is write-once, is
 * not on any read path, and can be dropped/truncated at any time, so a merge,
 * a mistaken delete or a wrong dedupe decision is always recoverable.
 */
class ExamResultArchive
{
    /** Removed because an identical row already existed. */
    public const REASON_DUPLICATE = 'duplicate';

    /** Removed by the dedupe, but the two rows disagreed on a mark. */
    public const REASON_CONFLICT = 'conflict';

    /** Removed by a teacher/admin through the UI. */
    public const REASON_MANUAL = 'manual';

    /** Removed because the pupil's record was deleted. */
    public const REASON_PUPIL_DELETED = 'pupil_deleted';

    /** Column order of `exam_results`, kept in one place for inserts. */
    private const COLUMNS = [
        'id', 'term', 'mid_term_mark', 'mid_term_raw', 'mid_term_max',
        'end_of_term_mark', 'end_term_raw', 'end_term_max', 'comments',
        'subject_id', 'pupil_id', 'created_at', 'updated_at',
    ];

    /**
     * Copy $results into the archive and delete exactly those rows, atomically.
     * Returns the number of rows archived (== deleted).
     *
     * @param  iterable<ExamResult|array>  $results
     */
    public static function move(iterable $results, string $reason = self::REASON_MANUAL, ?int $userId = null): int
    {
        $rows = [];
        $ids  = [];

        foreach ($results as $result) {
            $row = ['kept_id' => null];

            foreach (self::COLUMNS as $column) {
                $row[$column] = is_array($result) ? ($result[$column] ?? null) : $result->{$column};
            }

            $row['reason']      = $reason;
            $row['archived_at'] = now();
            $row['archived_by'] = $userId;

            $rows[] = $row;
            $ids[]  = $row['id'];
        }

        if (empty($rows)) {
            return 0;
        }

        DB::transaction(function () use ($rows, $ids) {
            foreach (array_chunk($rows, 500) as $chunk) {
                DB::table('exam_results_archive')->insert($chunk);
            }

            ExamResult::whereIn('id', $ids)->delete();
        });

        return count($rows);
    }

    /**
     * Archive everything matching the given query, then delete it.
     *
     * @return int  number of rows archived
     */
    public static function moveQuery(\Illuminate\Database\Eloquent\Builder $query, string $reason = self::REASON_MANUAL, ?int $userId = null): int
    {
        return self::move($query->get(), $reason, $userId);
    }
}
