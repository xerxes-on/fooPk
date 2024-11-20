<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailMailable extends Mailable
{
    use Queueable;
    use SerializesModels;

    protected $mailTemplate;

    protected $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $mailTemplate, $data)
    {
        $this->mailTemplate = $mailTemplate;
        $this->data         = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view($this->mailTemplate)
            ->with($this->data);
    }

    /**
     * Send the message using the given mailer.
     *
     * @param \Illuminate\Contracts\Mail\Factory|\Illuminate\Contracts\Mail\Mailer $mailer
     * @return \Illuminate\Mail\SentMessage|null
     */
    public function send($mailer)
    {
        $result = parent::send($mailer);
        if (config('mail.smtp.queue_enable_delay')) {
            sleep(config('mail.smtp.queue_delay'));
        }
        return $result;
    }

}
