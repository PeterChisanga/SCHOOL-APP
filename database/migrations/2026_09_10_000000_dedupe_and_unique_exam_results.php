<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A pupil may only have ONE result per subject per term.
 *
 * Before enforcing that with a unique index, any existing duplicates are
 * archived (never dropped) into `exam_results_archive` and then removed:
 *
 *  - keeper  = the row with the HIGHEST total marks (mid + end of term), so a
 *              merge never lowers a pupil's recorded result; ties go to the most
 *              recently updated row, then the highest id;
 *  - reason  = 'duplicate' when every mark in the group agreed,
 *              'conflict'  when a non-null mark differed from the keeper's.
 *
 * Conflicting groups are kept out of `exam_results` (the unique index needs one
 * row per key) but survive in the archive with `reason = 'conflict'` and
 * `kept_id` pointing at the row that won, so they can be reviewed and restored
 * with plain SQL instead of being silently lost.
 *
 * Three statements total — set-based on purpose: the previous row-by-row
 * implementation issued ~6,000 queries and took minutes on real production data.
 */
return new class extends Migration
{
    private const ARCHIVE = 'exam_results_archive';

    private const UNIQUE = 'exam_results_pupil_subject_term_unique';

    private const REASON_DUPLICATE = 'duplicate';

    public function up(): void
    {
        $this->createArchive();

        // 1. Archive every row that is NOT the keeper. INSERT IGNORE keeps this
        //    re-runnable if a previous attempt died between the two statements.
        DB::statement(<<<'SQL'
            INSERT IGNORE INTO exam_results_archive (
                id, term, mid_term_mark, mid_term_raw, mid_term_max,
                end_of_term_mark, end_term_raw, end_term_max, comments,
                subject_id, pupil_id, created_at, updated_at,
                kept_id, reason, archived_at, archived_by
            )
            SELECT
                r.id, r.term, r.mid_term_mark, r.mid_term_raw, r.mid_term_max,
                r.end_of_term_mark, r.end_term_raw, r.end_term_max, r.comments,
                r.subject_id, r.pupil_id, r.created_at, r.updated_at,
                k.keep_id, k.reason, NOW(), NULL
            FROM exam_results r
            INNER JOIN (
                SELECT
                    pupil_id,
                    subject_id,
                    term,
                    SUBSTRING_INDEX(
                        GROUP_CONCAT(
                            id ORDER BY
                                (COALESCE(mid_term_mark, 0) + COALESCE(end_of_term_mark, 0)) DESC,
                                updated_at DESC,
                                id DESC
                        ), ',', 1
                    ) AS keep_id,
                    CASE
                        WHEN COUNT(DISTINCT mid_term_mark)    > 1
                          OR COUNT(DISTINCT mid_term_raw)     > 1
                          OR COUNT(DISTINCT mid_term_max)     > 1
                          OR COUNT(DISTINCT end_of_term_mark) > 1
                          OR COUNT(DISTINCT end_term_raw)     > 1
                          OR COUNT(DISTINCT end_term_max)     > 1
                          OR COUNT(DISTINCT comments)         > 1
                        THEN 'conflict'
                        ELSE 'duplicate'
                    END AS reason
                FROM exam_results
                GROUP BY pupil_id, subject_id, term
                HAVING COUNT(*) > 1
            ) k
                ON  k.pupil_id   = r.pupil_id
                AND k.subject_id = r.subject_id
                AND k.term       = r.term
            WHERE r.id <> k.keep_id
        SQL);

        // 2. Delete exactly what was archived — never anything else.
        DB::statement(<<<'SQL'
            DELETE FROM exam_results
            WHERE id IN (
                SELECT id FROM exam_results_archive
                WHERE reason IN ('duplicate', 'conflict')
            )
        SQL);

        // 3. Enforce one result per pupil/subject/term from now on. This is what
        //    stops the duplicate-entry bug from ever recurring.
        if (! $this->hasUniqueIndex()) {
            Schema::table('exam_results', function (Blueprint $table) {
                $table->unique(['pupil_id', 'subject_id', 'term'], self::UNIQUE);
            });
        }
    }

    public function down(): void
    {
        // Deliberately keeps `exam_results_archive`: a rollback must never be
        // the thing that destroys data.
        Schema::table('exam_results', function (Blueprint $table) {
            $table->dropUnique(self::UNIQUE);
        });
    }

    private function createArchive(): void
    {
        if (! Schema::hasTable(self::ARCHIVE)) {
            // LIKE copies the columns and indexes but, importantly, no foreign
            // keys: deleting a pupil must not cascade into the archive.
            DB::statement('CREATE TABLE exam_results_archive LIKE exam_results');

            // If the source table already carried the unique index, LIKE copied
            // it: the archive must be able to hold every historical row for the
            // same pupil/subject/term, so the constraint has to go.
            if ($this->hasIndex(self::ARCHIVE)) {
                Schema::table(self::ARCHIVE, function (Blueprint $table) {
                    $table->dropUnique(self::UNIQUE);
                });
            }
        }

        if (Schema::hasColumn(self::ARCHIVE, 'kept_id')) {
            return;
        }

        Schema::table(self::ARCHIVE, function (Blueprint $table) {
            $table->unsignedBigInteger('kept_id')->nullable()->after('pupil_id');
            $table->string('reason', 20)->default(self::REASON_DUPLICATE);
            $table->timestamp('archived_at')->nullable();
            $table->unsignedBigInteger('archived_by')->nullable();

            // Access paths: restore/review one pupil's term, and list conflicts.
            $table->index(['pupil_id', 'subject_id', 'term'], 'exam_results_archive_pupil_term_index');
            $table->index('reason', 'exam_results_archive_reason_index');
        });
    }

    private function hasUniqueIndex(): bool
    {
        return $this->hasIndex('exam_results');
    }

    private function hasIndex(string $table): bool
    {
        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('index_name', self::UNIQUE)
            ->exists();
    }
};
