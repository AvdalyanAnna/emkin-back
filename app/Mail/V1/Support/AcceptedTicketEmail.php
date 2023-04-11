<?php

namespace App\Mail\V1\Support;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use JetBrains\PhpStorm\Pure;

/**
 * Class AcceptedTicketEmail
 * @package App\Mail\V1
 */
class AcceptedTicketEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(private string $name) { }

    /**
     * Get the message envelope.
     *
     * @return Envelope
     */
    public function envelope(): Envelope {
        return new Envelope(
            from: config('app.corporate_mail'),
            subject: 'Support Accepted Ticket Email',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return Content
     */
    #[Pure] public function content(): Content {
        return new Content(
            view: 'emails.accepted-ticket',
            markdown: 'emails.accepted-ticket',
            with: ['name' => $this->name]
        );
    }
}
