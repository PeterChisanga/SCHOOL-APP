<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per (class, term, day, period) — the weekly timetable for a term.
 *
 * There is deliberately NO uniqueness on (teacher_id, day, period): an admin is
 * allowed to double-book a teacher, the UI just highlights it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetable_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->string('term', 20);
            $table->unsignedTinyInteger('day');     // 1 = Monday
            $table->unsignedTinyInteger('period');  // 1 = first period of the day
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->timestamp('published_at')->nullable();

            $table->timestamps();

            // One cell per class/term/day/period: makes every write an upsert.
            $table->unique(['class_id', 'term', 'day', 'period'], 'timetable_class_term_slot_unique');
            $table->index(['school_id', 'term', 'published_at'], 'timetable_school_term_index');
            $table->index(['teacher_id', 'day'], 'timetable_teacher_day_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_entries');
    }
};
