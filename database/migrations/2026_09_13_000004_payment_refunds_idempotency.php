<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Refund support + webhook idempotency guard.
 * - refunded_amount tracks partial refunds per payment (additive).
 * - UNIQUE(stripe_checkout_session_id) makes duplicate webhook delivery
 *   structurally impossible (nullable columns allow multiple NULLs).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'refunded_amount')) {
                $table->decimal('refunded_amount', 12, 2)->default(0)->after('amount');
            }
        });
        // Unique index may already exist on some installs; guard it per driver.
        $exists = false;
        if (DB::getDriverName() === 'sqlite') {
            $exists = (bool) DB::selectOne(
                "SELECT 1 FROM sqlite_master WHERE type='index' AND name='payments_stripe_checkout_session_id_unique'"
            );
        } else {
            $db = DB::getDatabaseName();
            $exists = (bool) DB::selectOne(
                'SELECT 1 FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?',
                [$db, 'payments', 'payments_stripe_checkout_session_id_unique']
            );
        }
        if (!$exists) {
            Schema::table('payments', function (Blueprint $table) {
                $table->unique('stripe_checkout_session_id', 'payments_stripe_checkout_session_id_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique('payments_stripe_checkout_session_id_unique');
            $table->dropColumn('refunded_amount');
        });
    }
};
