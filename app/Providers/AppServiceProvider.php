<?php

namespace App\Providers;

use App\Models\Dealership;
use App\Models\Lead;
use App\Models\Task;
use App\Models\User;
use App\Observers\AuditLogObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        User::observe(AuditLogObserver::class);
        Dealership::observe(AuditLogObserver::class);
        Lead::observe(AuditLogObserver::class);
        Task::observe(AuditLogObserver::class);
    }
}
