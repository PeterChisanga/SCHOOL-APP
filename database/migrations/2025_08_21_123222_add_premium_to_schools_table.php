<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up() {
        Schema::table('schools', function (Blueprint $table) {
            $table->boolean('is_premium')->default(false)->after('address');
            $table->timestamp('subscription_expires_at')->nullable()->after('is_premium');;
        });
    }

    public function down() {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn(['is_premium', 'subscription_expires_at']);
        });
    }
};
