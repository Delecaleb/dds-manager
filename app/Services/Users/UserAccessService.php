<?php

namespace App\Services\Users;

use App\Models\User;
use App\Notifications\UserInvitation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * How an account gets and loses access. Admins never choose or see a password:
 *  - invite: the account starts with an unusable random password, and the user gets an
 *    email to choose their own (valid for the "invites" broker's lifetime, 7 days);
 *  - password reset: the user gets the standard reset email (valid 60 minutes);
 *  - revoke: the account is deactivated and every signed-in session and "remember me"
 *    cookie stops working at once, not at the next login.
 */
class UserAccessService
{
    public const INVITE_BROKER = 'invites';

    /** A password nobody knows, for an account whose owner has not chosen one yet. */
    public function unusablePassword(): string
    {
        return Str::password(64);
    }

    /**
     * Email the user a link to choose their password. Any earlier invite link stops working.
     *
     * @throws \Throwable when the mail cannot be sent; the account is left as it was
     */
    public function invite(User $user, User $invitedBy): void
    {
        $broker = Password::broker(self::INVITE_BROKER);
        $token = $broker->createToken($user);

        $user->notify(new UserInvitation($token, $invitedBy->name, $this->inviteDays()));
    }

    /**
     * Email the user the standard password reset link.
     *
     * @return string a Password::* status (RESET_LINK_SENT, RESET_THROTTLED, ...)
     */
    public function sendPasswordReset(User $user): string
    {
        return Password::broker()->sendResetLink(['email' => $user->email]);
    }

    /** Deactivate the account and sign it out everywhere. */
    public function revoke(User $user): void
    {
        $user->forceFill(['is_active' => false])->save();
        $this->endSessions($user);
    }

    public function restore(User $user): void
    {
        $user->forceFill(['is_active' => true])->save();
    }

    /**
     * Sign the user out of every browser: their stored sessions are deleted (database
     * sessions) and their "remember me" token is rotated. With other session drivers the
     * EnsureUserIsActive middleware signs a revoked user out on their next request.
     */
    public function endSessions(User $user): void
    {
        $user->forceFill(['remember_token' => Str::random(60)])->save();

        if (config('session.driver') === 'database') {
            DB::connection(config('session.connection'))
                ->table((string) config('session.table', 'sessions'))
                ->where('user_id', $user->getKey())
                ->delete();
        }
    }

    public function inviteDays(): int
    {
        return max(1, intdiv((int) config('auth.passwords.'.self::INVITE_BROKER.'.expire', 10080), 60 * 24));
    }
}
