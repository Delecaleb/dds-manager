<?php

use App\Http\Middleware\EnsureModuleAccess;
use App\Http\Middleware\EnsureSuperAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'module' => EnsureModuleAccess::class,
            'super_admin' => EnsureSuperAdmin::class,
        ]);

        // The tracking beacon is posted cross-origin by visitors' browsers on other
        // domains, so it cannot carry a session CSRF token. It is safe to exempt because
        // it is sent without cookies and only ever writes tracking rows for the site whose
        // public key it names — it can neither read nor act as a logged-in user.
        $middleware->validateCsrfTokens(except: ['t/collect']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
