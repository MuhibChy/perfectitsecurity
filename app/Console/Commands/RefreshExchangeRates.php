<?php

namespace App\Console\Commands;

use App\Services\CurrencyService;
use Illuminate\Console\Command;

class RefreshExchangeRates extends Command
{
    protected $signature = 'fx:refresh {--remote : Attempt remote Frankfurter API}';
    protected $description = 'Refresh currency exchange rates';

    public function handle(CurrencyService $fx): int
    {
        $fx->refreshRates((bool) $this->option('remote'));
        $this->info('Exchange rates refreshed.');
        return self::SUCCESS;
    }
}
