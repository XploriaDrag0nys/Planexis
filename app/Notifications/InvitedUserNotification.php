<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;

class InvitedUserNotification extends Notification
{
    protected $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject(Lang::get('Vous êtes invité à rejoindre Planexis'))
            ->line('Bonjour ! Vous avez été invité(e) à rejoindre un projet chez Planexis.')
            ->action('Configurer votre mot de passe', $url)
            ->line('Ce lien expirera dans 60 minutes.')
            ->line('Si vous n’avez pas demandé cette invitation, vous pouvez ignorer ce mail.');
    }
}
