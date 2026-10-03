<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceCountryPrice;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * AssetLifecycleCategorySeeder — adds the non-duplicative service category
 * "IT Asset, Endpoint & Lifecycle Management" with demo service listings.
 *
 * Idempotent: category matched by slug; services matched by slug; country
 * prices matched by (service, country). Safe to re-run — no duplicates.
 * Demo services carry is_demo=true (removable via demo:cleanup).
 */
class AssetLifecycleCategorySeeder extends Seeder
{
    public const CATEGORY_SLUG = 'it-asset-endpoint-lifecycle';

    public function run(): void
    {
        $category = ServiceCategory::firstOrCreate(
            ['slug' => self::CATEGORY_SLUG],
            [
                'name' => 'IT Asset, Endpoint & Lifecycle Management',
                'description' => 'Device inventory, endpoint configuration, software licence tracking, patch planning, lifecycle management and asset reporting.',
                'icon' => 'asset',
                'color' => '#0E7490',
                'sort_order' => 12,
                'is_active' => true,
            ]
        );

        $countries = [
            'UK' => Country::where('code', 'UK')->first(),
            'US' => Country::where('code', 'US')->first(),
            'BD' => Country::where('code', 'BD')->first(),
        ];
        if (in_array(null, $countries, true)) {
            $this->command?->warn('UK/US/BD country rows missing — skipping asset lifecycle services.');

            return;
        }

        $services = [
            ['Computer & Device Inventory Audit', 'Full audit of desktops, laptops and peripherals with ownership records and asset register.', 'fixed', 149, 119, 17500],
            ['Endpoint Configuration & Maintenance', 'Standard endpoint builds, configuration baselines and scheduled maintenance checks.', 'fixed', 199, 159, 23500],
            ['Software Inventory & Licence Tracking', 'Software discovery, licence reconciliation and renewal planning across the estate.', 'starting_from', 249, 199, 29500],
            ['Patch-Management Planning', 'Patch review cadence, testing approach and deployment planning for OS and applications.', 'fixed', 179, 139, 21000],
            ['Device Lifecycle Management', 'Procurement-to-retirement lifecycle tracking with refresh planning and disposal records.', 'starting_from', 299, 239, 35500],
            ['Asset Documentation & Reporting', 'Asset register setup, warranty tracking and periodic asset health reporting.', 'fixed', 129, 99, 15500],
        ];

        foreach ($services as $i => [$name, $short, $type, $us, $uk, $bd]) {
            $slug = Str::slug($name);
            $service = Service::firstOrCreate(
                ['slug' => $slug],
                [
                    'category_id' => $category->id,
                    'name' => $name,
                    'short_description' => $short,
                    'description' => $short.' Delivered remotely with written findings; onsite available where scheduled.',
                    'price_type' => $type,
                    'complexity_level' => 'standard',
                    'starting_price' => $us,
                    'allows_custom_quote' => true,
                    'is_featured' => false,
                    'is_active' => true,
                    'is_demo' => true,
                    'sort_order' => 10 + $i,
                    'features' => ['Written findings report', 'Asset register update', 'Follow-up recommendations'],
                    'tags' => ['assets', 'endpoint', 'lifecycle', 'demo'],
                ]
            );
            foreach (['UK' => $uk, 'US' => $us, 'BD' => $bd] as $code => $price) {
                ServiceCountryPrice::firstOrCreate(
                    ['service_id' => $service->id, 'country_id' => $countries[$code]->id],
                    ['pricing_type' => $type, 'price' => $price, 'is_active' => true]
                );
            }
        }

        $this->command?->info('Asset lifecycle category + 6 demo services ready (idempotent).');
    }
}
