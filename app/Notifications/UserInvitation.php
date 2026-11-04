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
        $email = $notifiable->getEmailForPasswordReset();
        $url = route('invitation.show', ['token' => $this->token, 'email' => $email]);
        // First name only; until they accept, the name is the one the admin typed or one made from the email.
        $firstName = trim(strtok((string) $notifiable->name, ' ') ?: '');
        $days = $this->expiresInDays.' '.($this->expiresInDays === 1 ? 'day' : 'days');

        return (new MailMessage)
            ->subject("Your {$app} account is ready")
            ->greeting($firstName !== '' ? "Hello {$firstName}," : 'Hello,')
            ->line("{$this->invitedBy} has given you access to {$app}.")
            ->line("To get started, set a password for your account. You will sign in with this email address ({$email}) and the password you choose.")
            ->action('Set your password', $url)
            ->line("For your security, this link can be used once and expires in {$days}. If it has expired, ask {$this->invitedBy} to send you a new invitation.")
            ->line('Not expecting this email? You can ignore it. No one can sign in to the account until a password is set.');
    }
}
