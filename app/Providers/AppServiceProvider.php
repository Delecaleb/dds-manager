<?php

namespace App\Providers;

use App\Domain\Support\ClinicRegistry;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Single, request-scoped clinic identity map (multi-office source of truth).
        $this->app->singleton(ClinicRegistry::class);
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
    }
}
