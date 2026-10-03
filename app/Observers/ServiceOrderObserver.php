<?php

namespace App\Observers;

use App\Models\ServiceOrder;

/**
 * Freezes the catalogue service details onto the order at creation, so
 * later catalogue edits never rewrite what the customer ordered.
 */
class ServiceOrderObserver
{
    public function creating(ServiceOrder $order): void
    {
        try {
            if (empty($order->service_snapshot) && $order->service_id) {
                $service = $order->service ?? \App\Models\Service::find($order->service_id);
                if ($service) {
                    $order->service_snapshot = [
                        'service_id' => $service->id,
                        'name' => $service->name,
                        'category' => $service->category?->name,
                        'starting_price' => $service->starting_price,
                        'price_type' => $service->price_type ?? null,
                        'snapshot_at' => now()->toDateTimeString(),
                    ];
                }
            }
        } catch (\Throwable $e) {
        }
    }
}
