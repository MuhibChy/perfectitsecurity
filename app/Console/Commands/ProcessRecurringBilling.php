<?php

namespace App\Console\Commands;

use App\Services\RecurringBillingService;
use Illuminate\Console\Command;

class ProcessRecurringBilling extends Command
{
    protected $signature = 'billing:process-subscriptions';
    protected $description = 'Generate invoices for due recurring subscriptions';

    public function handle(RecurringBillingService $billing): int
    {
        $result = $billing->processDueSubscriptions();
        $this->info("Created {$result['created']} invoices; {$result['failed']} failed.");
        return self::SUCCESS;
    }
}
