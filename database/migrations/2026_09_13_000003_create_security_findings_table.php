<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Security findings / vulnerability tracker (CISA KEV-capable, CVSS/EPSS
 * fields, MITRE ATT&CK technique reference, full status workflow).
 * Additive table; no existing data touched.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_findings', function (Blueprint $table) {
            $table->id();
            $table->string('finding_number')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('cve', 32)->nullable()->index();
            $table->enum('severity', ['critical', 'high', 'medium', 'low', 'info'])->default('medium')->index();
            $table->decimal('cvss_score', 3, 1)->nullable();
            $table->string('cvss_vector', 128)->nullable();
            $table->string('cvss_version', 8)->nullable();
            $table->decimal('epss_score', 5, 4)->nullable();
            $table->boolean('is_known_exploited')->default(false)->index();
            $table->string('affected_asset')->nullable();
            $table->string('affected_version', 64)->nullable();
            $table->string('mitre_technique', 16)->nullable();
            $table->enum('status', ['open', 'triaged', 'in_progress', 'mitigated', 'resolved', 'verified', 'accepted_risk', 'false_positive'])->default('open')->index();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('due_date')->nullable();
            $table->text('evidence')->nullable();
            $table->text('remediation')->nullable();
            $table->string('discovered_source', 64)->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_findings');
    }
};
