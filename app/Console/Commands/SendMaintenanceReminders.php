<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\ServiceMaintenance;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SendMaintenanceReminders extends Command
{
    protected $signature = 'maintenance:remind-due';

    protected $description = 'Notify customers and assignees of maintenance due within 7 days (idempotent per day)';

    public function handle(): int
    {
        $due = ServiceMaintenance::with(['customer', 'assignee', 'project'])
            ->whereIn('status', ['scheduled', 'active'])
            ->whereNotNull('next_due_at')
            ->whereDate('next_due_at', '<=', today()->addDays(7))
            ->get();

        $count = 0;
        foreach ($due as $m) {
            // Idempotent: one reminder per maintenance per day.
            $already = Notification::where('type', 'maintenance_due')
                ->where('notifiable_type', User::class)
                ->whereDate('created_at', today())
                ->where('data->maintenance_id', $m->id)
                ->exists();
            if ($already) {
                continue;
            }

            $msg = "Maintenance '{$m->title}' is due ".$m->next_due_at->format('d M Y').'.';
            foreach (array_filter([$m->customer_id, $m->assigned_to]) as $uid) {
                Notification::create([
                    'id' => (string) Str::uuid(),
                    'type' => 'maintenance_due',
                    'notifiable_type' => User::class,
                    'notifiable_id' => $uid,
                    'data' => ['title' => 'Maintenance due', 'message' => $msg, 'maintenance_id' => $m->id],
                    'read_at' => null,
                ]);
            }
            $count++;
        }

        $this->info("Maintenance reminders sent for {$count} plan(s).");

        return self::SUCCESS;
    }
}
