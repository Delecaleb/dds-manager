<?php

use App\Http\Middleware\AuthenticateApiKey;
use App\Http\Middleware\EnsureModuleAccess;
use App\Http\Middleware\EnsureSuperAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'module' => EnsureModuleAccess::class,
            'super_admin' => EnsureSuperAdmin::class,
            'api.key' => AuthenticateApiKey::class,
        ]);

        // The Developer API's rate limit is per key, so the key must be resolved first.
        // Without this, Laravel's priority list runs throttling before it.
        $middleware->prependToPriorityList(before: ThrottleRequests::class, prepend: AuthenticateApiKey::class);

        // Already-logged-in users hitting a guest page (e.g. /login) go to their own
        // landing module, not a hard-coded dashboard they may not have access to.
        $middleware->redirectUsersTo(fn (Request $request) => route($request->user()->homeRoute()));

        // The tracking beacon is posted cross-origin by visitors' browsers on other
        // domains, so it cannot carry a session CSRF token. It is safe to exempt because
        // it is sent without cookies and only ever writes tracking rows for the site whose
        // public key it names — it can neither read nor act as a logged-in user.
        $middleware->validateCsrfTokens(except: ['t/collect']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // The Developer API always answers in JSON (validation errors, 404s, 500s), whatever
        // Accept header the caller sent.
        $exceptions->shouldRenderJsonWhen(fn (Request $request) => $request->is('api/*') || $request->expectsJson());
    })->create();
