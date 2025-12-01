<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCommentsToExamResultsTable extends Migration
{
    public function up()
    {
        Schema::table('exam_results', function (Blueprint $table) {
            $table->string('comments')->nullable()->after('end_of_term_mark');
        });
    }

    public function down()
    {
        Schema::table('exam_results', function (Blueprint $table) {
            $table->dropColumn('comments');
        });
    }
}
