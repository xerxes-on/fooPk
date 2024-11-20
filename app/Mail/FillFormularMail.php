<?php

namespace App\Mail;

use Carbon\CarbonInterval;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\{Address, Content, Envelope};

/**
 * Email to notify user to fill formular.
 *
 * @used-by \App\Jobs\NotifyUserToFillQuestionnaireJob::handle()
 * @package App\Mail
 */
final class FillFormularMail extends Mailable implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string                 $lang,
        public readonly string $email,
        public readonly string $userName,
        public readonly string $resetToken
    ) {
        $this->locale($lang)->onQueue('emails');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            to: [new Address($this->email, $this->userName)],
            subject: trans('email.formular.missing.subject'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.notifyUserToFillFormular',
            with: [
                'userName'  => $this->userName,
                'actionUrl' => url(
                    config('app.url_meinplan') . route('password.reset', $this->resetToken, false)
                ),
                /** @note `days` is correct attribute existing in carbon. It's not a type! */
                'passwordExpirationDays' => (CarbonInterval::minutes((config('auth.passwords.users.expire')))->total('dayz'))
            ],
        );
    }
}
