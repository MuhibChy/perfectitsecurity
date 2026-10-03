<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Authoritative ISO country code for currency resolution.
     * Additive only: nullable, no backfill assumptions — the
     * CustomerCurrencyService keeps resolving from the legacy free-text
     * `country` field until an explicit code is stored.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'country_code')) {
                $table->string('country_code', 2)->nullable()->after('country');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'country_code')) {
                $table->dropColumn('country_code');
            }
        });
    }
};
