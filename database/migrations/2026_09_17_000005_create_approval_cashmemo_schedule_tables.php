<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Approver attribution for the account approval workflow.
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('approved_by')->nullable()->after('is_active')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->string('approval_note', 1000)->nullable()->after('approved_at');
        });

        // Cash memos: document headers referencing the authoritative
        // payment. Never money themselves; one memo per payment.
        Schema::create('cash_memos', function (Blueprint $table) {
            $table->id();
            $table->string('cash_memo_number')->unique();
            $table->foreignId('payment_id')->unique()->constrained('payments')->cascadeOnDelete();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();
        });

        // Planned stage payments per order. Actuals always come from
        // Payment rows (optionally linked via schedule_id).
        Schema::create('order_payment_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('service_orders')->cascadeOnDelete();
            $table->foreignId('milestone_id')->nullable()->constrained('project_milestones')->nullOnDelete();
            $table->string('title');
            $table->decimal('expected_amount', 14, 2);
            $table->decimal('paid_amount', 14, 2)->default(0);
            $table->string('status')->default('pending');
            $table->date('due_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['order_id', 'status']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('schedule_id')->nullable()->after('service_order_id')->constrained('order_payment_schedules')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('schedule_id');
        });
        Schema::dropIfExists('order_payment_schedules');
        Schema::dropIfExists('cash_memos');
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['approved_at', 'approval_note']);
        });
    }
};
