<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ITSM benchmark extension (additive only) — ServiceNow ITSM parity gaps.
 *
 * Adds ONLY new tables; touches no existing table, column, or record:
 * - problems + problem_ticket pivot (Problem Management: recurring incidents,
 *   root-cause investigation, workarounds, known errors).
 * - itsm_changes + itsm_change_approvals (Change Management lite: risk/impact
 *   assessment, implementation + rollback plans, approvals, maintenance
 *   windows, implementation results). Named itsm_* to avoid any clash with
 *   the existing service_change_requests (service-order price/change flow).
 * - assets + configuration_items + ci_relationships (Asset Management +
 *   lightweight CMDB: inventory, ownership, CI dependencies, links to
 *   tickets/problems/changes).
 * - remote_sessions (Remote support: request, schedule, explicit customer
 *   consent, technician authorisation, session record, audit — no fake
 *   remote-access capability is claimed; provider link only).
 * - site_visits (Onsite support: scheduling, assignment, check-in/out,
 *   work performed, parts used, customer confirmation).
 * - service_agreements (Managed services: per-customer scope, coverage
 *   hours, targets, renewals).
 * - sla_breach_logs (persistent SLA breach ledger; existing tickets.sla_*
 *   timestamps remain the source of deadlines).
 * - service_approvals (generic approval workflow for ITSM objects).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('problems', function (Blueprint $table) {
            $table->id();
            $table->string('problem_number', 20)->unique();
            $table->string('title', 255);
            $table->text('description');
            $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->string('category', 100)->nullable();
            $table->string('priority', 20)->default('medium');
            $table->string('impact', 20)->default('medium');
            $table->string('status', 30)->default('open'); // open|investigating|known_error|resolved|closed
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('root_cause')->nullable();
            $table->text('workaround')->nullable();
            $table->text('resolution')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'priority']);
            $table->index('customer_id');
            $table->index('assigned_to');
        });

        Schema::create('problem_ticket', function (Blueprint $table) {
            $table->id();
            $table->foreignId('problem_id')->constrained('problems')->cascadeOnDelete();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['problem_id', 'ticket_id']);
        });

        Schema::create('itsm_changes', function (Blueprint $table) {
            $table->id();
            $table->string('change_number', 20)->unique();
            $table->string('title', 255);
            $table->text('description');
            $table->string('type', 20)->default('normal'); // standard|normal|emergency
            $table->string('risk', 20)->default('medium'); // low|medium|high
            $table->string('impact', 20)->default('medium');
            $table->string('status', 30)->default('requested'); // requested|assessed|approved|scheduled|implementing|completed|failed|cancelled
            $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('implementation_plan')->nullable();
            $table->text('rollback_plan')->nullable();
            $table->timestamp('scheduled_start')->nullable();
            $table->timestamp('scheduled_end')->nullable();
            $table->timestamp('implemented_at')->nullable();
            $table->text('implementation_result')->nullable();
            $table->text('failure_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'risk']);
            $table->index('customer_id');
            $table->index('assigned_to');
        });

        Schema::create('itsm_change_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('itsm_change_id')->constrained('itsm_changes')->cascadeOnDelete();
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('decision', 20)->default('pending'); // pending|approved|rejected
            $table->text('comments')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
            $table->index('itsm_change_id');
        });

        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_tag', 40)->unique();
            $table->string('name', 255);
            $table->string('category', 100)->nullable();
            $table->string('manufacturer', 120)->nullable();
            $table->string('model', 120)->nullable();
            $table->string('serial_number', 160)->nullable();
            $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->string('location', 255)->nullable();
            $table->foreignId('assigned_to_user')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('in_stock'); // in_stock|deployed|maintenance|retired
            $table->date('purchase_date')->nullable();
            $table->date('warranty_expires')->nullable();
            $table->date('retirement_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'category']);
            $table->index('customer_id');
            $table->index('serial_number');
        });

        Schema::create('configuration_items', function (Blueprint $table) {
            $table->id();
            $table->string('ci_number', 20)->unique();
            $table->string('name', 255);
            $table->string('ci_type', 40)->default('other'); // server|workstation|network|application|cloud|dns|backup|other
            $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('asset_id')->nullable()->constrained('assets')->nullOnDelete();
            $table->string('identifier', 255)->nullable(); // IP / hostname / URL
            $table->string('environment', 40)->default('production');
            $table->string('status', 30)->default('active'); // active|maintenance|decommissioned
            $table->string('criticality', 20)->default('medium');
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('details')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['ci_type', 'status']);
            $table->index('customer_id');
            $table->index('asset_id');
        });

        Schema::create('ci_relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_ci_id')->constrained('configuration_items')->cascadeOnDelete();
            $table->foreignId('child_ci_id')->constrained('configuration_items')->cascadeOnDelete();
            $table->string('relationship_type', 40)->default('depends_on'); // depends_on|hosts|runs_on|connects_to
            $table->timestamps();
            $table->unique(['parent_ci_id', 'child_ci_id', 'relationship_type'], 'ci_rel_unique');
        });

        Schema::create('remote_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_number', 20)->unique();
            $table->foreignId('ticket_id')->nullable()->constrained('tickets')->nullOnDelete();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('provider', 40)->default('support_link'); // support_link|teamviewer|anydesk|other
            $table->string('session_url', 500)->nullable(); // join link only — never credentials
            $table->boolean('consent_given')->default(false);
            $table->timestamp('consent_at')->nullable();
            $table->foreignId('consent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('requested'); // requested|scheduled|active|completed|expired|cancelled
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->text('outcome')->nullable();
            $table->timestamps();
            $table->index(['status', 'scheduled_at']);
            $table->index('customer_id');
            $table->index('ticket_id');
        });

        Schema::create('site_visits', function (Blueprint $table) {
            $table->id();
            $table->string('visit_number', 20)->unique();
            $table->foreignId('ticket_id')->nullable()->constrained('tickets')->nullOnDelete();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->string('address', 500);
            $table->timestamp('scheduled_at')->nullable();
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('scheduled'); // scheduled|en_route|on_site|completed|cancelled
            $table->timestamp('check_in_at')->nullable();
            $table->timestamp('check_out_at')->nullable();
            $table->text('work_performed')->nullable();
            $table->text('parts_used')->nullable();
            $table->string('customer_signature_name', 255)->nullable();
            $table->timestamp('customer_confirmed_at')->nullable();
            $table->text('follow_up_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'scheduled_at']);
            $table->index('customer_id');
            $table->index('technician_id');
        });

        Schema::create('service_agreements', function (Blueprint $table) {
            $table->id();
            $table->string('agreement_number', 20)->unique();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->string('title', 255);
            $table->text('scope')->nullable();
            $table->string('coverage_hours', 255)->nullable();
            $table->unsignedInteger('response_target_minutes')->nullable();
            $table->unsignedInteger('resolution_target_minutes')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->string('status', 30)->default('draft'); // draft|active|suspended|expired|terminated
            $table->timestamp('renewal_reminder_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'ends_at']);
            $table->index('customer_id');
        });

        Schema::create('sla_breach_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->foreignId('sla_policy_id')->nullable()->constrained('sla_policies')->nullOnDelete();
            $table->string('breach_type', 20); // response|resolution
            $table->timestamp('deadline')->nullable();
            $table->timestamp('breached_at')->useCurrent();
            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('acknowledged_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['ticket_id', 'breach_type']);
            $table->index('breached_at');
        });

        Schema::create('service_approvals', function (Blueprint $table) {
            $table->id();
            $table->string('approval_number', 20)->unique();
            $table->string('approvable_type', 255);
            $table->unsignedBigInteger('approvable_id');
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 40)->default('other');
            $table->string('status', 20)->default('pending'); // pending|approved|rejected|cancelled
            $table->text('comments')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
            $table->index(['approvable_type', 'approvable_id']);
            $table->index(['status', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_approvals');
        Schema::dropIfExists('sla_breach_logs');
        Schema::dropIfExists('service_agreements');
        Schema::dropIfExists('site_visits');
        Schema::dropIfExists('remote_sessions');
        Schema::dropIfExists('ci_relationships');
        Schema::dropIfExists('configuration_items');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('itsm_change_approvals');
        Schema::dropIfExists('itsm_changes');
        Schema::dropIfExists('problem_ticket');
        Schema::dropIfExists('problems');
    }
};
