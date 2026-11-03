<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sign out a user whose access was revoked while they were signed in. Login already
 * refuses inactive accounts; this closes the gap for sessions that were open at the time
 * (UserAccessService::revoke also deletes database sessions, but not every driver can).
 */
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && ! $user->is_active) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return $request->expectsJson()
                ? response()->json(['message' => 'Your access has been revoked.'], 401)
                : redirect()->route('login')->withErrors(['email' => 'Your access has been revoked. Contact your administrator.']);
        }

        return $next($request);
    }
}
