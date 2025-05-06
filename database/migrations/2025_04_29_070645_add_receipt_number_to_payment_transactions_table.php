<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->string('receipt_number')->nullable()->after('payment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropColumn('receipt_number');
        });
    }
};
