<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\Ticket::class => \App\Policies\TicketPolicy::class,
        \App\Models\Project::class => \App\Policies\ProjectPolicy::class,
        \App\Models\Invoice::class => \App\Policies\InvoicePolicy::class,
        \App\Models\Quotation::class => \App\Policies\QuotationPolicy::class,
        \App\Models\Service::class => \App\Policies\ServicePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Define a super-admin gate for admin-only operations
        Gate::define('is-super-admin', function ($user) {
            return $user->isSuperAdmin();
        });

        // Define a finance gate
        Gate::define('manage-finance', function ($user) {
            return $user->isFinanceManager();
        });

        // Define a support gate
        Gate::define('manage-support', function ($user) {
            return $user->isSupportAgent();
        });
    }
}
