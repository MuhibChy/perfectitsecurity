<?php

namespace App\Console\Commands;

use App\Models\Contract;
use Illuminate\Console\Command;

class ExpireContracts extends Command
{
    protected $signature = 'contracts:expire';

    protected $description = 'Mark active contracts past their end date as expired';

    public function handle(): int
    {
        $count = Contract::where('status', 'active')
            ->whereNotNull('end_date')
            ->where('end_date', '<', now()->startOfDay())
            ->update(['status' => 'expired']);

        $this->info("Contracts expired: {$count}.");

        return self::SUCCESS;
    }
}
