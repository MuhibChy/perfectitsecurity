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
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
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
