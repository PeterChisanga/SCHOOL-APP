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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 10, 2);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->string('type'); // e.g., 'School Fees', 'Lunch Fees', 'Transport Fees'
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('pupil_id')->constrained('pupils')->onDelete('cascade');
            $table->string('term'); // e.g., 'Term 1', 'Term 2', etc.
            $table->decimal('balance', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
