<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Bind Ollama MCP service
        $this->app->singleton(\App\Services\OllamaMcpService::class, function ($app) {
            return new \App\Services\OllamaMcpService();
        });
        // Voice-provider rail (§10): manual logging by default; swap the
        // implementation here to plug in a telephony vendor — no other
        // application code names a provider.
        $this->app->bind(\App\Contracts\CallProvider::class, \App\Services\ManualCallProvider::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Status-transition audit: every important status change lands in
        // the existing audit log with old/new values and actor.
        foreach ([
            \App\Models\ServiceOrder::class, \App\Models\Project::class,
            \App\Models\Task::class, \App\Models\Ticket::class,
            \App\Models\Invoice::class, \App\Models\Quotation::class,
        ] as $model) {
            $model::observe(\App\Observers\StatusHistoryObserver::class);
        }
        \App\Models\ServiceOrder::observe(\App\Observers\ServiceOrderObserver::class);

        // Backup status widget for the admin health center (admin-only views).
        \Illuminate\Support\Facades\View::composer('admin.health.index', function ($view) {
            try {
                $last = \App\Models\Backup::active()->latest()->first();
                $view->with('backupWidget', [
                    'last_id' => $last?->backup_id,
                    'last_at' => $last?->created_at,
                    'verified' => (bool) $last?->isVerified(),
                    'failed' => \App\Models\Backup::active()->where('status', 'failed')->count(),
                ]);
            } catch (\Throwable) {
                $view->with('backupWidget', null);
            }
        });
    }
}
