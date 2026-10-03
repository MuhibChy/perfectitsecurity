<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->foreignId('quotation_id')->nullable()->after('service_id')->constrained('quotations')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable()->after('closed_at');
            $table->string('cancel_reason', 1000)->nullable()->after('cancelled_at');
            $table->foreignId('cancelled_by')->nullable()->after('cancel_reason')->constrained('users')->nullOnDelete();
            $table->boolean('refund_due')->default(false)->after('cancelled_by');
        });
    }

    public function down(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('quotation_id');
            $table->dropConstrainedForeignId('cancelled_by');
            $table->dropColumn(['cancelled_at', 'cancel_reason', 'refund_due']);
        });
    }
};
