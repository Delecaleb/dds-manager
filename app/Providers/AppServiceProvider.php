<?php

namespace App\Providers;

use App\Domain\Support\ClinicRegistry;
use App\Models\User;
use App\Services\Sync\QueueHealthService;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\Looping;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Single, request-scoped clinic identity map (multi-office source of truth).
        $this->app->scoped(ClinicRegistry::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Grant all gates automatically to Super Admin
        Gate::before(function (User $user) {
            if ($user->isSuperAdmin()) {
                return true;
            }
        });

        // Gate specifically for user and access privilege management
        Gate::define('manage-users', function (User $user): bool {
            return $user->isSuperAdmin();
        });

        // Gate for checking module-level permissions
        Gate::define('access-module', function (User $user, string $module): bool {
            return $user->hasModuleAccess($module);
        });

        // Worker heartbeat for the Sync Manager's queue health check: proves a
        // sync worker is alive, whether it is taking a job or polling an empty queue.
        Queue::before(function (JobProcessing $event): void {
            if ($event->connectionName === config('sync.queue.connection')) {
                app(QueueHealthService::class)->recordWorkerAlive($event->job->resolveName());
            }
        });

        Queue::looping(function (Looping $event): void {
            if ($event->connectionName === config('sync.queue.connection')) {
                app(QueueHealthService::class)->recordWorkerAlive();
            }
        });
    }
}
