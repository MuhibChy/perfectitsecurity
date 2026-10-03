<?php

namespace App\Services;

use App\Models\Quotation;
use App\Models\Service;
use App\Models\ServiceCountryPrice;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Configurable promotional-discount engine (additive).
 *
 * Configuration lives in the settings store (group `promo`), editable from
 * Admin → Settings → Promotion:
 *   promo.enabled, promo.name, promo.percent, promo.category_ids (JSON),
 *   promo.starts_at, promo.ends_at, promo.timezone, promo.terms.
 *
 * Rules enforced server-side:
 * - Inactive/expired campaigns never discount (display + quotes).
 * - Only explicitly eligible categories; quote-based services excluded.
 * - Never stacks on an already-discounted country price (no double discount).
 * - Taxes/fees computed AFTER discount on the discounted subtotal only for
 *   new quotes; historical quotes keep their stored snapshot untouched.
 * - Customers can never set prices: staff recompute everything server-side.
 */
class PromotionService
{
    public const SETTINGS_GROUP = 'promo';

    public static function defaults(): array
    {
        return [
            'promo.enabled' => '0',
            'promo.name' => 'Support Services Promotion',
            'promo.percent' => '33',
            'promo.category_ids' => json_encode([]),
            'promo.starts_at' => '',
            'promo.ends_at' => '',
            'promo.timezone' => 'UTC',
            'promo.terms' => 'Promotional discount applies to eligible fixed-price support services only. Excludes taxes, third-party costs and payment fees. One discount per order; cannot be combined with other offers.',
        ];
    }

    /** Raw campaign config (strings, as stored). */
    public function campaign(): array
    {
        $stored = Setting::group(self::SETTINGS_GROUP);

        return array_merge(self::defaults(), $stored);
    }

    public function percent(): float
    {
        return max(0, min(100, (float) ($this->campaign()['promo.percent'] ?? 0)));
    }

    /** @return int[] */
    public function eligibleCategoryIds(): array
    {
        $raw = $this->campaign()['promo.category_ids'] ?? '[]';
        $ids = json_decode(is_string($raw) ? $raw : json_encode($raw), true);

        return array_values(array_filter(array_map('intval', (array) $ids)));
    }

    public function window(): array
    {
        $cfg = $this->campaign();
        $tz = $cfg['promo.timezone'] ?: 'UTC';
        $parse = function ($v) use ($tz) {
            if (! $v) {
                return null;
            }
            try {
                return Carbon::parse($v, $tz);
            } catch (\Throwable $e) {
                return null;
            }
        };

        return ['starts_at' => $parse($cfg['promo.starts_at']), 'ends_at' => $parse($cfg['promo.ends_at']), 'timezone' => $tz];
    }

    public function isActive(?Carbon $now = null): bool
    {
        $cfg = $this->campaign();
        if (($cfg['promo.enabled'] ?? '0') !== '1') {
            return false;
        }
        if ($this->percent() <= 0) {
            return false;
        }
        $now = $now ?? Carbon::now();
        ['starts_at' => $start, 'ends_at' => $end] = $this->window();
        if ($start && $now->lt($start)) {
            return false;
        }
        if ($end && $now->gt($end)) {
            return false;
        }

        return true;
    }

    public function isEligibleService(Service $service): bool
    {
        if (! $this->isActive()) {
            return false;
        }
        if (! $service->is_active) {
            return false;
        }
        if (! in_array((int) $service->category_id, $this->eligibleCategoryIds(), true)) {
            return false;
        }
        $type = strtolower((string) ($service->price_type ?? ''));
        if (in_array($type, ['custom_quote', 'custom', ''], true)) {
            return false;
        }

        return true;
    }

    /**
     * Price breakdown for one country price row. Never stacks: when the row
     * already carries its own live discount, the promo is skipped.
     */
    public function priceFor(Service $service, ServiceCountryPrice $row): array
    {
        $original = (float) $row->price;
        $rowHasDiscount = $row->discount_price
            && $row->discount_valid_until
            && Carbon::parse($row->discount_valid_until)->isFuture();

        if ($rowHasDiscount) {
            return [
                'applies' => false, 'reason' => 'row_discount',
                'original' => $original, 'percent' => 0.0,
                'discount' => 0.0, 'final' => (float) $row->effective_price,
            ];
        }
        if (! $this->isEligibleService($service)) {
            return [
                'applies' => false, 'reason' => 'ineligible',
                'original' => $original, 'percent' => 0.0,
                'discount' => 0.0, 'final' => $original,
            ];
        }
        $percent = $this->percent();
        $discount = round($original * $percent / 100, 2);

        return [
            'applies' => true, 'reason' => 'promo',
            'original' => $original, 'percent' => $percent,
            'discount' => $discount, 'final' => round($original - $discount, 2),
        ];
    }

    /**
     * Attach the promotion to a draft quotation (staff action, server-side
     * recompute). Throws (422) when the campaign is inactive, the service is
     * ineligible, or the quote already carries a promotion (no double apply).
     */
    public function applyToQuotation(Quotation $quotation, int $serviceId, int $countryId, int $staffUserId): Quotation
    {
        if ($quotation->status !== 'draft') {
            abort(422, 'Promotions can only be applied to draft quotations.');
        }
        if (! empty($quotation->promo_campaign)) {
            abort(422, 'This quotation already carries a promotion.');
        }
        if (! $this->isActive()) {
            abort(422, 'No active promotion campaign.');
        }
        $service = Service::findOrFail($serviceId);
        $row = ServiceCountryPrice::where('service_id', $service->id)
            ->where('country_id', $countryId)->where('is_active', true)->firstOrFail();
        $breakdown = $this->priceFor($service, $row);
        if (! $breakdown['applies']) {
            abort(422, 'Service is not eligible for the current promotion.');
        }

        return DB::transaction(function () use ($quotation, $service, $row, $breakdown, $staffUserId) {
            $cfg = $this->campaign();
            $quotation->items()->create([
                'description' => "Promotional discount — {$cfg['promo.name']} ({$breakdown['percent']}% off {$service->name})",
                'quantity' => 1,
                'unit_price' => 0,
                'discount' => $breakdown['discount'],
                'total' => -$breakdown['discount'],
            ]);
            $quotation->promo_campaign = $cfg['promo.name'];
            $quotation->promo_percent = $breakdown['percent'];
            $quotation->promo_snapshot = [
                'service_id' => $service->id,
                'country_id' => $row->country_id,
                'currency' => $row->country?->currency_code ?? 'USD',
                'original_price' => $breakdown['original'],
                'discount_amount' => $breakdown['discount'],
                'final_price' => $breakdown['final'],
                'applied_at' => now()->toIso8601String(),
                'applied_by' => $staffUserId,
            ];
            // NOTE: subtotal is the NET sum of item totals (existing convention:
            // item-level discounts live inside item totals; the header
            // discount_amount is informational). The negative promo line keeps
            // total = subtotal + tax correct without double counting.
            $quotation->subtotal = round((float) $quotation->items()->sum('total'), 2);
            $quotation->tax_amount = round($quotation->subtotal * ((float) $quotation->tax_rate / 100), 2);
            $quotation->total = round($quotation->subtotal + $quotation->tax_amount, 2);
            $quotation->save();

            return $quotation->fresh();
        });
    }
}
