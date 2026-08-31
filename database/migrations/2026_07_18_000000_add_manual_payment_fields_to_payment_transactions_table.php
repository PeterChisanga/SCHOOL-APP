<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            // 'mobile_money' (existing Lenco flow) or 'manual' (bank transfer / reference / proof upload)
            $table->string('payment_method')->default('mobile_money')->after('mode_of_payment');

            // pending -> awaiting admin review (manual only)
            // verified -> admin approved, balance updated
            // rejected -> admin rejected
            // successful -> mobile money flow completed (kept separate so old logic isn't disturbed)
            $table->string('status')->default('successful')->after('payment_method');

            // What the parent typed in as their own reference (e.g. bank transaction ID, MoMo txn code)
            $table->string('parent_reference')->nullable()->after('receipt_number');

            // Path (on the 'public' disk) to an uploaded screenshot/receipt, if provided
            $table->string('proof_of_payment_path')->nullable()->after('parent_reference');

            $table->unsignedBigInteger('verified_by')->nullable()->after('proof_of_payment_path');
            $table->timestamp('verified_at')->nullable()->after('verified_by');
            $table->string('rejection_reason')->nullable()->after('verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropColumn([
                'payment_method',
                'status',
                'parent_reference',
                'proof_of_payment_path',
                'verified_by',
                'verified_at',
                'rejection_reason',
            ]);
        });
    }
};