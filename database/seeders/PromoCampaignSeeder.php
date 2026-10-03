<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * PromoCampaignSeeder — seeds the default 33% promotional campaign config.
 * Idempotent and admin-respecting: existing keys are NEVER overwritten, so
 * re-running cannot reset staff edits, and historical quotes are untouched.
 */
class PromoCampaignSeeder extends Seeder
{
    public function run(): void
    {
        $eligibleSlugs = ['managed-it-support', 'microsoft-365-business-it', AssetLifecycleCategorySeeder::CATEGORY_SLUG];
        $eligibleIds = ServiceCategory::whereIn('slug', $eligibleSlugs)->pluck('id')->all();

        $defaults = [
            'promo.enabled' => '1',
            'promo.name' => 'Autumn IT Support Offer',
            'promo.percent' => '33',
            'promo.category_ids' => json_encode($eligibleIds),
            'promo.starts_at' => now()->toIso8601String(),
            'promo.ends_at' => now()->addDays(60)->toIso8601String(),
            'promo.timezone' => 'UTC',
            'promo.terms' => '33% off eligible fixed-price support services. Excludes taxes, third-party costs and payment fees. One discount per order; cannot be combined with other offers. Ends automatically on the published end date.',
        ];

        foreach ($defaults as $key => $value) {
            if (Setting::where('key', $key)->doesntExist()) {
                Setting::set($key, $value, 'promo');
            }
        }

        $this->command?->info('Promo campaign defaults ensured (existing staff edits preserved).');
    }
}
