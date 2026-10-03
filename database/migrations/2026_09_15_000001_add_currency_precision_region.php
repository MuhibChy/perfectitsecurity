<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * International expansion: decimal precision + region on countries,
     * plus catalog rows for Europe, USA, and the Middle East.
     * Additive only — existing GBP/USD/BDT rows are untouched.
     */
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            if (!Schema::hasColumn('countries', 'decimal_places')) {
                $table->unsignedTinyInteger('decimal_places')->default(2)->after('currency_name');
            }
            if (!Schema::hasColumn('countries', 'region')) {
                $table->string('region', 30)->nullable()->after('decimal_places');
            }
        });

        // Backfill regions for the original three rows (by code, never by id).
        DB::table('countries')->where('code', 'UK')->update(['region' => 'UK', 'decimal_places' => 2]);
        DB::table('countries')->where('code', 'US')->update(['region' => 'North America', 'decimal_places' => 2]);
        DB::table('countries')->where('code', 'BD')->update(['region' => 'South Asia', 'decimal_places' => 2]);

        $rows = [
            // code, name, currency, symbol, currency name, decimals, region, sort
            ['UK', 'United Kingdom', 'GBP', '£', 'British Pound', 2, 'UK', 1],
            ['US', 'United States', 'USD', '$', 'US Dollar', 2, 'North America', 2],
            ['BD', 'Bangladesh', 'BDT', '৳', 'Bangladeshi Taka', 2, 'South Asia', 3],
            ['DE', 'Germany', 'EUR', '€', 'Euro', 2, 'Europe', 10],
            ['FR', 'France', 'EUR', '€', 'Euro', 2, 'Europe', 11],
            ['NL', 'Netherlands', 'EUR', '€', 'Euro', 2, 'Europe', 12],
            ['IE', 'Ireland', 'EUR', '€', 'Euro', 2, 'Europe', 13],
            ['ES', 'Spain', 'EUR', '€', 'Euro', 2, 'Europe', 14],
            ['IT', 'Italy', 'EUR', '€', 'Euro', 2, 'Europe', 15],
            ['AE', 'United Arab Emirates', 'AED', 'د.إ', 'UAE Dirham', 2, 'Middle East', 20],
            ['SA', 'Saudi Arabia', 'SAR', 'ر.س', 'Saudi Riyal', 2, 'Middle East', 21],
            ['QA', 'Qatar', 'QAR', 'ر.ق', 'Qatari Riyal', 2, 'Middle East', 22],
            ['KW', 'Kuwait', 'KWD', 'د.ك', 'Kuwaiti Dinar', 3, 'Middle East', 23],
            ['BH', 'Bahrain', 'BHD', 'د.ب', 'Bahraini Dinar', 3, 'Middle East', 24],
            ['OM', 'Oman', 'OMR', 'ر.ع', 'Omani Rial', 3, 'Middle East', 25],
            ['JO', 'Jordan', 'JOD', 'د.ا', 'Jordanian Dinar', 3, 'Middle East', 26],
        ];
        foreach ($rows as [$code, $name, $ccy, $symbol, $ccyName, $dp, $region, $sort]) {
            // Insert-only: never overwrite seeder rows or admin toggles.
            if (!DB::table('countries')->where('code', $code)->exists()) {
                DB::table('countries')->insert(
                    ['code' => $code, 'name' => $name, 'currency_code' => $ccy, 'currency_symbol' => $symbol,
                        'currency_name' => $ccyName, 'decimal_places' => $dp, 'region' => $region,
                        'is_active' => true, 'sort_order' => $sort,
                        'created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }

    public function down(): void
    {
        DB::table('countries')->whereIn('code', ['DE', 'FR', 'NL', 'IE', 'ES', 'IT', 'AE', 'SA', 'QA', 'KW', 'BH', 'OM', 'JO'])->delete();
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn(['decimal_places', 'region']);
        });
    }
};
