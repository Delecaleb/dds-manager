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

        return $next($request);
    }
}
