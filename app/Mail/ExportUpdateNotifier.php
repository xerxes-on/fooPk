<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class ExportUpdateNotifier extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(private readonly string $attachmentPath)
    {
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            to: [new Address(config('mail.from.address'), config('mail.from.name'))],
            cc: [
                'barbara.kronseder@foodpunk.de',
                'marina.lommel@foodpunk.de',
                'tanja.treiner@foodpunk.de',
                'K.Chaliadzinskaya@ventionteams.com',
            ],
            subject: 'Export data from ' . now()->toDateString()
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(view: 'emails.seeAttached');
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [
            Attachment::fromStorage($this->attachmentPath)
        ];
    }
}
