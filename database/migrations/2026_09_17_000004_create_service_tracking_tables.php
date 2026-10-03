<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Central service timeline: event metadata + references (never
        // duplicated business records). Customer-visible rows are
        // deliberately published; everything else stays internal.
        Schema::create('service_events', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id');
            $table->foreignId('order_id')->nullable()->constrained('service_orders')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->text('reason')->nullable();
            $table->text('comment')->nullable();
            $table->boolean('customer_visible')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
            $table->index(['order_id', 'created_at']);
            $table->index(['project_id', 'created_at']);
            $table->index(['customer_id', 'created_at']);
            $table->index(['action', 'created_at']);
        });

        // Post-completion maintenance / follow-up plans.
        Schema::create('service_maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('service_orders')->nullOnDelete();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('type')->default('general');
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->string('frequency')->nullable();
            $table->date('next_due_at')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'status']);
            $table->index(['next_due_at', 'status']);
        });

        // Customer-initiated scope change requests (approval-gated).
        Schema::create('service_change_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained('service_orders')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('details');
            $table->decimal('price_impact', 12, 2)->nullable();
            $table->integer('time_impact_days')->nullable();
            $table->string('status')->default('requested');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->text('decision_note')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        // Service snapshot preserved at order creation (observer-filled).
        Schema::table('service_orders', function (Blueprint $table) {
            $table->json('service_snapshot')->nullable()->after('service_id');
        });

        // Project comments are shown in the customer portal: default keeps
        // current behavior; false marks staff-internal notes.
        Schema::table('project_comments', function (Blueprint $table) {
            $table->boolean('is_customer_visible')->default(true)->after('comment');
        });

        // Pause tracking for running-time honesty.
        Schema::table('tasks', function (Blueprint $table) {
            $table->timestamp('paused_at')->nullable()->after('start_date');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('paused_at');
        });
        Schema::table('project_comments', function (Blueprint $table) {
            $table->dropColumn('is_customer_visible');
        });
        Schema::table('service_orders', function (Blueprint $table) {
            $table->dropColumn('service_snapshot');
        });
        Schema::dropIfExists('service_change_requests');
        Schema::dropIfExists('service_maintenances');
        Schema::dropIfExists('service_events');
    }
};
