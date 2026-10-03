<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Secure member identity foundation (additive only).
 * - users.member_number: unique server-generated CUS-/EMP-/… (never user-supplied)
 * - users.identity_status / identity_verified_at: verification lifecycle state
 * - users.presence / presence_visible: availability display + privacy
 * - identity_documents: private verification submissions, history preserved
 * - member_id_cards: issued cards with revocable random tokens (history kept)
 */
return new class extends Migration
{
    public const PREFIXES = [
        'customer' => 'CUS', 'employee' => 'EMP', 'freelancer' => 'FRL',
        'commission_agent' => 'AGT', 'sales_agent' => 'SAL',
        'support_agent' => 'SUP', 'support_manager' => 'SUP',
        'project_manager' => 'PMG', 'finance_manager' => 'FIN',
        'training_manager' => 'TRN', 'admin' => 'ADM', 'super_admin' => 'ADM',
    ];

    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('member_number')->nullable()->unique()->after('id');
            $table->string('identity_status', 32)->default('not_started')->after('verification_status');
            $table->timestamp('identity_verified_at')->nullable()->after('identity_status');
            $table->string('presence', 16)->default('offline')->after('identity_verified_at');
            $table->boolean('presence_visible')->default(true)->after('presence');
            $table->index('identity_status');
        });

        Schema::create('identity_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('document_type', 64);
            $table->string('issuing_country', 3)->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('path', 255);
            $table->string('original_name', 255)->nullable();
            $table->string('status', 32)->default('submitted');
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });

        Schema::create('member_id_cards', function (Blueprint $table) {
            $table->id();
            $table->string('card_number')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // Random verification token: only its hash is stored (never the token).
            $table->string('token_hash', 64)->unique();
            $table->string('status', 16)->default('active');
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('revoked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('revoke_reason')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });

        // Backfill member numbers deterministically: PREFIX-<id padded>.
        foreach (\App\Models\User::orderBy('id')->get(['id', 'role']) as $user) {
            $prefix = self::PREFIXES[$user->role] ?? 'MBR';
            \App\Models\User::where('id', $user->id)->update([
                'member_number' => $prefix.'-'.str_pad((string) $user->id, 6, '0', STR_PAD_LEFT),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('member_id_cards');
        Schema::dropIfExists('identity_documents');
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['member_number']);
            $table->dropIndex(['identity_status']);
            $table->dropColumn(['member_number', 'identity_status', 'identity_verified_at', 'presence', 'presence_visible']);
        });
    }
};
