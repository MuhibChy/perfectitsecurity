<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Quotation;
use Illuminate\Console\Command;

class ExpireQuotations extends Command
{
    protected $signature = 'quotes:expire';
    protected $description = 'Mark sent quotations past their validity date as expired';

    public function handle(): int
    {
        $stale = Quotation::where('status', 'sent')
            ->whereNotNull('valid_until')
            ->where('valid_until', '<', now()->startOfDay())
            ->get();

        foreach ($stale as $q) {
            $q->update(['status' => 'expired']);
            AuditLog::log('quotation.expired', 'quotations', $q, "Quotation {$q->quotation_number} expired (valid until {$q->valid_until->format('Y-m-d')}).");
        }

        $this->info("Quotations expired: {$stale->count()}.");

        return self::SUCCESS;
    }
}
