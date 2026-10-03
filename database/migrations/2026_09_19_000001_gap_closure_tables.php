<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gap-closure round (additive only).
 * - users.last_activity_at (throttled middleware updates; login stays separate)
 * - salaries: effective_from/effective_to/currency/approved_by/approved_at
 *   (versioning: new rows per change, history preserved, no overwrites)
 * - expenses: paid_at + payment_reference (approval ≠ payment; paid links
 *   the single payment transaction, never a second expense entry)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_activity_at')->nullable()->after('last_login_at');
        });

        Schema::table('salaries', function (Blueprint $table) {
            $table->date('effective_from')->nullable()->after('pay_date');
            $table->date('effective_to')->nullable()->after('effective_from');
            $table->string('currency', 3)->default('USD')->after('effective_to');
            $table->foreignId('approved_by')->nullable()->after('notes')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->index(['user_id', 'effective_from']);
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->timestamp('paid_at')->nullable()->after('approved_at');
            $table->string('payment_reference')->nullable()->after('paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn(['paid_at', 'payment_reference']);
        });
        Schema::table('salaries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['effective_from', 'effective_to', 'currency', 'approved_at']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('last_activity_at');
        });
    }
};
