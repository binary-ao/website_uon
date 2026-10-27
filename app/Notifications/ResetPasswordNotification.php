<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $url = route('password.reset', ['token' => $this->token, 'email' => $notifiable->getEmailForPasswordReset()]);
        $minutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject('Recuperação de palavra-passe — '.setting('site_name'))
            ->greeting('Olá,')
            ->line('Recebemos um pedido de recuperação da palavra-passe da sua conta no CMS.')
            ->action('Definir nova palavra-passe', $url)
            ->line("Esta ligação expira em {$minutes} minutos.")
            ->line('Se não fez este pedido, ignore esta mensagem.')
            ->salutation(setting('site_name'));
    }
}
