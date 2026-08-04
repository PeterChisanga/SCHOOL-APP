<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_details', function (Blueprint $table) {
            $table->id();

            // One row per school. unique() lets you upsert safely and means
            // ->first() by school_id always returns the right (only) record.
            $table->unsignedBigInteger('school_id')->unique();

            // Bank transfer details
            $table->string('bank_name')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_branch')->nullable();
            $table->string('bank_swift_code')->nullable();

            // Mobile money merchant details
            $table->string('mobile_money_provider')->nullable(); // e.g. MTN, Airtel, Zamtel
            $table->string('mobile_money_number')->nullable();
            $table->string('mobile_money_account_name')->nullable();

            // Free-text instructions shown to parents on the manual payment page
            $table->text('payment_instructions')->nullable();

            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_details');
    }
};