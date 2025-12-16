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
        Schema::create('timetables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->foreignId('time_slot_id')->constrained('time_slots')->onDelete('cascade');
            $table->enum('day', ['Mon','Tue','Wed','Thu','Fri']);
            $table->string('room')->nullable();
            $table->string('term')->default('Term 1');
            $table->string('year')->default((string)date('Y'));
            $table->timestamps();

            // Constraints to help prevent conflicts
            $table->unique(['class_id','day','time_slot_id','term','year'], 'unique_class_slot');
            $table->unique(['teacher_id','day','time_slot_id','term','year'], 'unique_teacher_slot');
            $table->unique(['room','day','time_slot_id','term','year'], 'unique_room_slot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timetables');
    }
};

