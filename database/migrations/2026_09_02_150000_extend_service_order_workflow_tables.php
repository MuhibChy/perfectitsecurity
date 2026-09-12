<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Extend service_orders with negotiation, verification snapshot, manager override and lock fields.
        Schema::table('service_orders', function (Blueprint $table) {
            $table->string('order_source_label')->nullable()->after('source');
            $table->string('urgency')->default('medium')->after('requirements');
            $table->date('preferred_date')->nullable()->after('urgency');
            $table->text('customer_notes')->nullable()->after('preferred_date');
            $table->text('internal_notes')->nullable()->after('customer_notes');
            $table->boolean('price_locked')->default(false)->after('customer_accepted_at');
            $table->foreignId('final_price_accepted_by')->nullable()->constrained('users')->nullOnDelete()->after('price_locked');
            $table->string('verification_snapshot')->nullable()->after('final_price_accepted_by');
            $table->foreignId('manager_override_by')->nullable()->constrained('users')->nullOnDelete()->after('verification_snapshot');
            $table->timestamp('manager_override_at')->nullable()->after('manager_override_by');
            $table->text('manager_override_reason')->nullable()->after('manager_override_at');
            $table->text('attachments')->nullable()->after('manager_override_reason');
            $table->text('closure_notes')->nullable()->after('closed_at');
            $table->timestamp('task_completed_at')->nullable()->after('closure_notes');
        });

        // Extend tasks with SLA / technical workflow fields.
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('ticket_id')->nullable()->constrained('tickets')->nullOnDelete()->after('service_order_id');
            $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete()->after('ticket_id');
            $table->timestamp('start_date')->nullable()->after('deadline');
            $table->integer('estimated_minutes')->nullable()->after('start_date');
            $table->integer('actual_minutes')->nullable()->after('estimated_minutes');
            $table->text('technical_notes')->nullable()->after('actual_minutes');
            $table->timestamp('completed_at')->nullable()->after('technical_notes');
            $table->string('sla_priority')->nullable()->after('completed_at');
        });

        // Extend expenses with task linkage and labour / commission cost tracking.
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('task_id')->nullable()->constrained('tasks')->nullOnDelete()->after('service_order_id');
            $table->foreignId('worker_id')->nullable()->constrained('users')->nullOnDelete()->after('task_id');
            $table->string('cost_type')->default('vendor')->after('worker_id');
            $table->decimal('hours', 8, 2)->nullable()->after('cost_type');
            $table->decimal('hourly_rate', 12, 2)->nullable()->after('hours');
            $table->decimal('commission_percentage', 5, 2)->nullable()->after('hourly_rate');
            $table->decimal('commission_amount', 12, 2)->nullable()->after('commission_percentage');

            $table->index(['service_order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex(['service_order_id', 'status']);
            $table->dropColumn(['commission_amount', 'commission_percentage', 'hourly_rate', 'hours', 'cost_type', 'worker_id', 'task_id']);
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['sla_priority', 'completed_at', 'technical_notes', 'actual_minutes', 'estimated_minutes', 'start_date', 'customer_id', 'ticket_id']);
        });

        Schema::table('service_orders', function (Blueprint $table) {
            $table->dropColumn([
                'order_source_label', 'urgency', 'preferred_date', 'customer_notes', 'internal_notes',
                'price_locked', 'final_price_accepted_by', 'verification_snapshot',
                'manager_override_by', 'manager_override_at', 'manager_override_reason',
                'attachments', 'closure_notes', 'task_completed_at',
            ]);
        });
    }
};
