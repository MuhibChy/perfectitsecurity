<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Individual-account architecture (additive only).
 *
 * One authoritative identity stays in `users`. This migration adds:
 * - user_settings (per-user KV: theme, locale, notifications, dashboard…)
 * - direct_messages (permission-scoped inbox, internal vs customer-visible)
 * - emergency_requests (CRITICAL/HIGH/NORMAL triage lane, audited)
 * - bank_transfers (controlled payout workflow with idempotency keys;
 *   COMPLETED always requires an external provider reference)
 * - franchises (+ users.franchise_id link for franchise-scoped reporting)
 * - task_contributors (multi-employee service contribution, §45)
 * - users: timezone/job_title/department/branch/employee_number (nullable)
 *
 * No existing table is altered beyond ADD COLUMN (nullable). No data loss.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('group')->default('general');
            $table->string('key');
            $table->text('value')->nullable();
            $table->string('type')->default('string');
            $table->timestamps();
            $table->unique(['user_id', 'group', 'key']);
        });

        Schema::create('direct_messages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('recipient_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('direct_messages')->nullOnDelete();
            $table->string('subject')->nullable();
            $table->text('body');
            // Internal staff notes are never shown to customers (mirrors ticket flags).
            $table->boolean('is_internal')->default(false);
            $table->boolean('is_customer_visible')->default(true);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['recipient_id', 'read_at']);
            $table->index(['sender_id', 'created_at']);
        });

        Schema::create('emergency_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            // CRITICAL | HIGH | NORMAL
            $table->string('severity')->default('NORMAL');
            $table->string('category')->default('incident');
            $table->text('description');
            // new|acknowledged|assigned|in_progress|resolved|closed
            $table->string('status')->default('new');
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'severity']);
        });

        Schema::create('franchises', function (Blueprint $table) {
            $table->id();
            $table->string('franchise_code')->unique();
            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
            $table->string('territory')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('timezone')->nullable()->after('country');
            $table->string('job_title')->nullable()->after('timezone');
            $table->string('department')->nullable()->after('job_title');
            $table->string('branch')->nullable()->after('department');
            $table->string('employee_number')->nullable()->after('branch');
            $table->foreignId('franchise_id')->nullable()->after('company_id')->constrained('franchises')->nullOnDelete();
        });

        Schema::create('bank_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            // Idempotency: retried callbacks/double-clicks reuse the key → one transfer.
            $table->string('idempotency_key')->unique();
            $table->foreignId('beneficiary_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('franchise_id')->nullable()->constrained('franchises')->nullOnDelete();
            // salary|commission|contractor|franchise_share|expense
            $table->string('purpose')->default('salary');
            $table->unsignedBigInteger('related_id')->nullable();
            $table->decimal('amount', 14, 2);
            $table->string('currency', 3)->default('USD');
            // sandbox|bank (sandbox = clearly-marked test rail, never real money)
            $table->string('provider')->default('sandbox');
            $table->string('external_reference')->nullable();
            // draft|pending_approval|approved|processing|completed|failed|cancelled|reversed
            $table->string('status')->default('draft');
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('executed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->string('masked_destination')->nullable();
            $table->timestamps();
            $table->index(['beneficiary_id', 'status']);
            $table->index(['status', 'purpose']);
        });

        Schema::create('task_contributors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('contributor');
            $table->timestamps();
            $table->unique(['task_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_contributors');
        Schema::dropIfExists('bank_transfers');
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('franchise_id');
            $table->dropColumn(['timezone', 'job_title', 'department', 'branch', 'employee_number']);
        });
        Schema::dropIfExists('franchises');
        Schema::dropIfExists('emergency_requests');
        Schema::dropIfExists('direct_messages');
        Schema::dropIfExists('user_settings');
    }
};
