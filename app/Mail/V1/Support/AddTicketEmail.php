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
 * Class AddTicketEmail
 * @package App\Mail\V1
 */
class AddTicketEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(private array $attributes) { }

    /**
     * Get the message envelope.
     *
     * @return Envelope
     */
    public function envelope(): Envelope {
        return new Envelope(
            from: $this->attributes['email'],
            subject: 'Support Add Ticket Email',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return Content
     */
    #[Pure] public function content(): Content {
        return new Content(
            view: 'emails.add-ticket',
            markdown: 'emails.add-ticket',
            with: [
                'name' => $this->attributes['name'],
                'email' => $this->attributes['email'],
                'description' => $this->attributes['description'],
                'theme' => $this->attributes['theme'] ?? '',
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array {
        if ($this->issetFiles()) {

            $attachFiles = [];

            foreach ($this->attributes['files'] as $file) {
                array_push($attachFiles, $file);
            }

            return $attachFiles;
        }

        return [];
    }

    /**
     * @return bool
     */
    public function issetFiles(): bool {
        return isset($this->attributes['files']);
    }
}
