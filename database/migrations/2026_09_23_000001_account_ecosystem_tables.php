<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Employee & Customer Account Ecosystem (additive only).
 *
 * One authoritative identity stays in `users` (with its member_number,
 * verification and presence systems untouched). This migration adds:
 * - profile_details (extended customer/employee contact, comms-preference
 *   and HR fields; one row per user; ALL nullable → safe for existing rows)
 * - employee_compensations (compensation-model CONFIGURATION: salary /
 *   commission / project / hybrid flags + values; actual money movement
 *   stays in salaries / commissions / commission_payouts / bank_transfers)
 * - employee_assignments (audited links employee → customer/project/order/
 *   service/ticket/task via morph; history preserved, never overwritten)
 * - call_logs (voice-communication records with swappable provider columns;
 *   provider defaults to `manual` — no telephony vendor hard-coded)
 * - direct_messages += related_type/related_id (optional link of a
 *   conversation to its ticket/project/order; nullable)
 * - project_members += agreed_amount/share_percent/paid_amount (project-based
 *   pay terms on the EXISTING membership pivot; nullable/zeroed)
 *
 * No existing table is altered beyond ADD COLUMN (nullable). No data loss.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            // Extended contact (§1-3).
            $table->string('secondary_phone', 30)->nullable();
            $table->string('whatsapp_number', 30)->nullable();
            // website|email|phone|whatsapp|video|sms
            $table->string('preferred_contact_method', 20)->nullable();
            $table->string('contact_hours', 100)->nullable();
            $table->string('availability_note', 255)->nullable();
            // Professional profile (§1B).
            $table->string('team', 100)->nullable();
            $table->json('skills')->nullable();
            $table->json('certifications')->nullable();
            $table->json('expertise')->nullable();
            // HR links (§1B/§30).
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('hire_date')->nullable();
            $table->date('termination_date')->nullable();
            $table->string('employment_status', 30)->nullable();
            $table->string('emergency_contact_name', 150)->nullable();
            $table->string('emergency_contact_phone', 30)->nullable();
            $table->string('emergency_contact_relation', 60)->nullable();
            // Business info + admin-only notes (§1A, privacy-gated in code).
            $table->text('business_info')->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('employee_compensations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            // Hybrid model flags (§4): any combination may be true.
            $table->boolean('has_salary')->default(false);
            $table->decimal('salary_amount', 14, 2)->nullable();
            // weekly|biweekly|monthly|yearly
            $table->string('salary_frequency', 20)->nullable();
            $table->date('salary_start_date')->nullable();
            $table->boolean('has_commission')->default(false);
            // percentage|fixed
            $table->string('commission_type', 20)->nullable();
            $table->decimal('commission_value', 14, 4)->nullable();
            $table->foreignId('commission_rule_id')->nullable()->constrained('commission_rules')->nullOnDelete();
            $table->boolean('has_project_pay')->default(false);
            $table->text('project_terms')->nullable();
            // active|suspended
            $table->string('status')->default('active');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('employee_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->morphs('assignable');
            $table->foreignId('assigned_by')->constrained('users')->restrictOnDelete();
            // active|completed|revoked
            $table->string('status')->default('active');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['employee_id', 'status']);
        });

        Schema::create('call_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('caller_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('recipient_id')->constrained('users')->cascadeOnDelete();
            // outbound|inbound
            $table->string('direction', 20)->default('outbound');
            // Provider abstraction (§10): `manual` default; a future
            // telephony vendor plugs in via CallProvider without schema change.
            $table->string('provider', 60)->default('manual');
            $table->string('provider_call_id', 120)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->unsignedInteger('duration_seconds')->default(0);
            // completed|missed|failed|voicemail|scheduled
            $table->string('outcome', 20)->default('completed');
            $table->nullableMorphs('related');
            $table->string('subject', 255)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_customer_visible')->default(true);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->index(['caller_id', 'started_at']);
            $table->index(['recipient_id', 'started_at']);
        });

        Schema::table('direct_messages', function (Blueprint $table) {
            $table->nullableMorphs('related');
        });

        Schema::table('project_members', function (Blueprint $table) {
            $table->decimal('agreed_amount', 14, 2)->nullable();
            $table->decimal('share_percent', 7, 4)->nullable();
            $table->decimal('paid_amount', 14, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('project_members', function (Blueprint $table) {
            $table->dropColumn(['agreed_amount', 'share_percent', 'paid_amount']);
        });
        Schema::table('direct_messages', function (Blueprint $table) {
            $table->dropMorphs('related');
        });
        Schema::dropIfExists('call_logs');
        Schema::dropIfExists('employee_assignments');
        Schema::dropIfExists('employee_compensations');
        Schema::dropIfExists('profile_details');
    }
};
