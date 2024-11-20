<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\App;

/**
 * Email that allows a user to reset a password.
 */
class MailResetPasswordToken extends Notification
{
    use Queueable;

    public $token;

    /**
     * Create a new notification instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        $url = route('password.reset', [$this->token, App::getLocale()]);
        return (new MailMessage())
            ->subject(trans('Reset Password Notification'))
            ->line(trans('You are receiving this email because we received a password reset request for your account.'))
            ->action(trans('Reset Password'), $url)
            ->line(trans('If you have not requested this e-mail, please contact us by replying to this e-mail.'));
    }
}
