<?php

namespace App\Services;

use App\Models\Country;
use App\Models\Holiday;
use Carbon\Carbon;

class BusinessHoursService
{
    /**
     * Add N business minutes respecting country business hours, weekdays, and holidays.
     */
    public function addBusinessMinutes(?Country $country, int $minutes, ?Carbon $from = null): Carbon
    {
        $cursor = ($from ? $from->copy() : now())->seconds(0);
        if ($minutes <= 0) {
            return $cursor;
        }

        $start = $country?->business_hours_start ? Carbon::parse($country->business_hours_start)->format('H:i') : '09:00';
        $end = $country?->business_hours_end ? Carbon::parse($country->business_hours_end)->format('H:i') : '17:00';
        $days = $country?->business_days ?: [1, 2, 3, 4, 5]; // Mon–Fri
        $days = array_map('intval', (array) $days);

        $remaining = $minutes;
        $guard = 0;

        while ($remaining > 0 && $guard < 20000) {
            $guard++;

            if (!$this->isBusinessDay($cursor, $country, $days)) {
                $cursor->addDay()->setTimeFromTimeString($start . ':00');
                continue;
            }

            $dayStart = $cursor->copy()->setTimeFromTimeString($start . ':00');
            $dayEnd = $cursor->copy()->setTimeFromTimeString($end . ':00');

            if ($cursor->lt($dayStart)) {
                $cursor = $dayStart->copy();
            }

            if ($cursor->gte($dayEnd)) {
                $cursor->addDay()->setTimeFromTimeString($start . ':00');
                continue;
            }

            $available = $cursor->diffInMinutes($dayEnd);
            if ($remaining <= $available) {
                $cursor->addMinutes($remaining);
                $remaining = 0;
            } else {
                $remaining -= $available;
                $cursor->addDay()->setTimeFromTimeString($start . ':00');
            }
        }

        return $cursor;
    }

    public function isBusinessDay(Carbon $date, ?Country $country, array $days): bool
    {
        if (!in_array((int) $date->dayOfWeekIso, $days, true)) {
            return false;
        }

        $query = Holiday::whereDate('date', $date->toDateString());
        if ($country) {
            $query->where(function ($q) use ($country) {
                $q->whereNull('country_id')->orWhere('country_id', $country->id);
            });
        } else {
            $query->whereNull('country_id');
        }

        return !$query->exists();
    }

    public function taxRateForCountry(?Country $country): float
    {
        return (float) ($country?->tax_rate ?? 0);
    }
}
