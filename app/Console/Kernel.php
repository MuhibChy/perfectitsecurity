<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('sla:check-deadlines')->everyFiveMinutes()->withoutOverlapping();
        $schedule->command('invoices:send-overdue-reminders')->daily()->at('09:00')->withoutOverlapping();
        $schedule->command('billing:process-subscriptions')->dailyAt('06:00')->withoutOverlapping()->onOneServer();
        $schedule->command('fx:refresh')->dailyAt('06:30')->withoutOverlapping()->onOneServer();
        $schedule->command('auth:clear-resets')->everyFifteenMinutes();
        $schedule->command('queue:prune-failed --hours=168')->weekly();
        // Automated backups (cron expressions configurable via config/backup.php; no code edits needed).
        $schedule->command('backup:run --type=full')->cron((string) config('backup.schedules.full', '0 2 * * *'))->withoutOverlapping()->onOneServer();
        $schedule->command('backup:run --type=db')->cron((string) config('backup.schedules.db', '0 */6 * * *'))->withoutOverlapping()->onOneServer();
        $schedule->command('backup:prune')->dailyAt('03:30')->withoutOverlapping()->onOneServer();
        $schedule->command('backup:monitor')->hourly()->withoutOverlapping()->onOneServer();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
