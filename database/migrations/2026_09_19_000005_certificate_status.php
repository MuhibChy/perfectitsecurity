<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Certificate lifecycle (§27): ACTIVE / REVOKED / EXPIRED with audit trail.
 * Additive only; existing certificates default to active.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_certificates', function (Blueprint $table) {
            $table->string('status', 16)->default('active')->after('completed_at');
            $table->timestamp('revoked_at')->nullable()->after('status');
            $table->foreignId('revoked_by')->nullable()->after('revoked_at')->constrained('users')->nullOnDelete();
            $table->text('revoke_reason')->nullable()->after('revoked_by');
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('training_certificates', function (Blueprint $table) {
            $table->dropConstrainedForeignId('revoked_by');
            $table->dropIndex(['user_id', 'status']);
            $table->dropColumn(['status', 'revoked_at', 'revoke_reason']);
        });
    }
};
