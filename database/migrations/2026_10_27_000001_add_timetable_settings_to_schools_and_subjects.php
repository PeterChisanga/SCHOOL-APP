<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Timetable settings live on the school (one place, editable from the
 * timetable screen) and each subject can point at the teacher who teaches it,
 * so a generated timetable already knows who is in the room.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->unsignedTinyInteger('periods_per_day')->default(8)->after('is_premium');
            $table->unsignedTinyInteger('days_per_week')->default(5)->after('periods_per_day');
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->foreignId('teacher_id')
                ->nullable()
                ->after('school_id')
                ->constrained('teachers')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('teacher_id');
        });

        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn(['periods_per_day', 'days_per_week']);
        });
    }
};
