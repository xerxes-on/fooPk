<?php

namespace Modules\Chargebee\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserHasTwoActiveChargebeeSubscriptionsNotification extends Notification
{
    use Queueable;

    public $user;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        //TODO kutas i18n
        return (new MailMessage())
            ->subject(__('A new user with a second Chargebee Subscription'))
            ->line(__('You have a new user, who needs a second Subscription. Check now'))
            ->line(__('Name:') . $this->user->full_name)
            ->line(__('Email:' . $this->user->email))
            ->action('View user', route('admin.model.update', ['adminModel' => 'users', 'adminModelId' => $this->user->id]));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
