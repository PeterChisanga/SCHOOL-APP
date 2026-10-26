<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A pupil may only have ONE result per subject per term. Before enforcing
     * that with a unique index, merge any existing duplicates: keep the most
     * recently updated row and fill any gaps in its marks from the others.
     */
    public function up(): void
    {
        $groups = DB::table('exam_results')
            ->select('pupil_id', 'subject_id', 'term')
            ->groupBy('pupil_id', 'subject_id', 'term')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        $fields = ['mid_term_mark', 'mid_term_raw', 'mid_term_max',
                   'end_of_term_mark', 'end_term_raw', 'end_term_max', 'comments'];

        foreach ($groups as $group) {
            $rows = DB::table('exam_results')
                ->where('pupil_id', $group->pupil_id)
                ->where('subject_id', $group->subject_id)
                ->where('term', $group->term)
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->get();

            $keeper = $rows->first();

            $merged = [];
            foreach ($fields as $field) {
                $merged[$field] = $keeper->$field;
            }

            foreach ($rows->slice(1) as $duplicate) {
                foreach ($fields as $field) {
                    if ($merged[$field] === null && $duplicate->$field !== null) {
                        $merged[$field] = $duplicate->$field;
                    }
                }
            }

            DB::table('exam_results')->where('id', $keeper->id)->update($merged);

            $remove = $rows->slice(1)->pluck('id')->all();
            if (! empty($remove)) {
                DB::table('exam_results')->whereIn('id', $remove)->delete();
            }
        }

        Schema::table('exam_results', function (Blueprint $table) {
            $table->unique(['pupil_id', 'subject_id', 'term'], 'exam_results_pupil_subject_term_unique');
        });
    }

    public function down(): void
    {
        Schema::table('exam_results', function (Blueprint $table) {
            $table->dropUnique('exam_results_pupil_subject_term_unique');
        });
    }
};
