<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('exam_results', function (Blueprint $table) {
            $table->decimal('mid_term_raw', 8, 2)->nullable()->after('mid_term_mark');
            $table->decimal('mid_term_max', 8, 2)->nullable()->after('mid_term_raw');
            $table->decimal('end_term_raw', 8, 2)->nullable()->after('end_of_term_mark');
            $table->decimal('end_term_max', 8, 2)->nullable()->after('end_term_raw');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_results', function (Blueprint $table) {
            $table->dropColumn(['mid_term_raw', 'mid_term_max', 'end_term_raw', 'end_term_max']);
        });
    }
};
