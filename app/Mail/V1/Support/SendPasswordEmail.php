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
 * Class SendPasswordEmail
 * @package App\Mail\V1\Support
 */
class SendPasswordEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(private string $full_name, private string $password) { }

    /**
     * Get the message envelope.
     *
     * @return Envelope
     */
    public function envelope(): Envelope {
        return new Envelope(
            from: config('app.corporate_mail'),
            subject: 'Registered Email',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return Content
     */
    #[Pure] public function content(): Content {
        return new Content(
            view: 'emails.send-password',
            markdown: 'emails.send-password',
            with: [
                'full_name' => $this->full_name,
                'password' => $this->password,
            ],
        );
    }
}
