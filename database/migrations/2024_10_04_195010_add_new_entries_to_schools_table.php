<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->string('motto')->after('name')->nullable();
            $table->string('email')->nullable()->after('motto');
            $table->string('phone')->nullable()->after('email');
            $table->string('photo')->nullable()->after('phone'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn('motto');
            $table->dropColumn('email');
            $table->dropColumn('phone');
            $table->dropColumn('photo');
        });
    }
};
