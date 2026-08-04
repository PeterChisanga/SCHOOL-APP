<?php
// database/migrations/2026_07_06_000001_add_lenco_credentials_to_schools.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->text('lenco_secret_key')->nullable(); // encrypted at rest via model cast
            $table->string('lenco_account_name')->nullable(); // display label, e.g. "St. Mary's Lenco Wallet"
            $table->boolean('lenco_enabled')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn(['lenco_secret_key', 'lenco_account_name', 'lenco_enabled']);
        });
    }
};