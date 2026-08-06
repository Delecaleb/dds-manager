<?php

namespace App\Providers;

use App\Domain\Support\ClinicRegistry;
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
        //
    }
}
