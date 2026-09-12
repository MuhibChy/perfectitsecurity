<?php

namespace App\Console\Commands;

use App\Services\SlaService;
use Illuminate\Console\Command;

class CheckSlaDeadlines extends Command
{
    protected $signature = 'sla:check-deadlines';
    protected $description = 'Send SLA warnings and escalate breached support tickets';

    public function handle(SlaService $slaService): int
    {
        $result = $slaService->processDeadlines();
        $this->info("SLA deadlines processed: {$result['warningCount']} warnings, {$result['breachCount']} breaches.");

        return self::SUCCESS;
    }
}
