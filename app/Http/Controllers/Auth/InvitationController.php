<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Users\UserAccessService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

/**
 * Accepting an invitation: the link from the invitation email opens a page to confirm
 * the user's name and choose a password. Accepting verifies the email (the link proves
 * the user can read it) and signs them in.
 */
class InvitationController extends Controller
{
    public function show(Request $request, string $token): View
    {
        $user = User::where('email', (string) $request->query('email'))->first();
        $broker = Password::broker(UserAccessService::INVITE_BROKER);

        $valid = $user !== null && $user->is_active && $broker->tokenExists($user, $token);

        return view('auth.accept-invitation', [
            'token' => $token,
            'email' => (string) $request->query('email'),
            'name' => $user?->name,
            'valid' => $valid,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::where('email', $request->input('email'))->first();

        if ($user === null || ! $user->is_active) {
            return back()->withErrors(['email' => 'This invitation is no longer valid. Ask your administrator for a new one.']);
        }

        $status = Password::broker(UserAccessService::INVITE_BROKER)->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                $user->forceFill([
                    'name' => $request->input('name'),
                    'password' => $request->input('password'),
                    'remember_token' => Str::random(60),
                    'email_verified_at' => $user->email_verified_at ?? now(),
                    'last_login_at' => now(),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withInput($request->only('email', 'name'))
                ->withErrors(['email' => 'This invitation link has expired or was replaced by a newer one. Ask your administrator to send a new invitation.']);
        }

        Auth::login($user->fresh());
        $request->session()->regenerate();

        return redirect()->route($user->fresh()->homeRoute())
            ->with('status', 'Welcome! Your password is set.');
    }
}
