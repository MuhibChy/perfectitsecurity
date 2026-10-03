<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Additive promotion snapshot on quotations (website population task).
 * Existing financial columns untouched; historical quotes keep working even
 * when no promotion is configured (all three columns stay NULL).
 * - promo_campaign: human campaign name, e.g. "Autumn Support Offer 2026".
 * - promo_percent: discount percent applied, e.g. 33.00.
 * - promo_snapshot: auditable JSON {service_id, country_id, original_price,
 *   discount_amount, final_price, currency, applied_at, applied_by}.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->string('promo_campaign', 160)->nullable()->after('discount_amount');
            $table->decimal('promo_percent', 5, 2)->nullable()->after('promo_campaign');
            $table->json('promo_snapshot')->nullable()->after('promo_percent');
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn(['promo_campaign', 'promo_percent', 'promo_snapshot']);
        });
    }
};
