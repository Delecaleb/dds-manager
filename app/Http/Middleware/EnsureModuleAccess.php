<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->hasModuleAccess($module)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Forbidden: You do not have permission to access this module.',
                ], 403);
            }

            abort(403, 'Access denied. You do not have permission to access the "'.$module.'" module.');
        }

        // Modules split into pages (ModuleManager `pages`) also need the page's permission.
        if (! $user->canOpenRoute($module, $request->route()?->getName())) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Forbidden: You do not have permission to access this page.',
                ], 403);
            }

            abort(403, 'Access denied. You do not have permission to access this page. Ask an administrator to grant it.');
        }

        return $next($request);
    }
}
