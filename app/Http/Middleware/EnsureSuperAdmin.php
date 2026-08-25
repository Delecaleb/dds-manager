<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->isSuperAdmin()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Forbidden: Super Administrator access required.',
                ], 403);
            }

            abort(403, 'Access denied. Only Super Administrators can manage users and access privileges.');
        }

        return $next($request);
    }
}
