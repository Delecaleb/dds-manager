<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * The email a new user gets: who invited them, and a link to choose their own password.
 * Sent at once (not queued) so the admin learns straight away if mail could not be sent.
 */
class UserInvitation extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $token,
        private readonly string $invitedBy,
        private readonly int $expiresInDays,
    ) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $app = (string) config('app.name');
        $url = route('invitation.show', ['token' => $this->token, 'email' => $notifiable->getEmailForPasswordReset()]);

        return (new MailMessage)
            ->subject("You've been invited to {$app}")
            ->greeting('Hello,')
            ->line("{$this->invitedBy} has created an account for you on {$app}.")
            ->line('Click the button below to choose your password and sign in.')
            ->action('Set my password', $url)
            ->line('This link works for '.$this->expiresInDays.' '.($this->expiresInDays === 1 ? 'day' : 'days').'. If it has expired, ask your administrator to send a new one.')
            ->line('If you were not expecting this invitation, you can ignore this email.');
    }
}
