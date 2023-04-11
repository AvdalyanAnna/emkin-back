<?php

namespace App\Services\V1\Chat;

use App\Models\Conversation;
use Exception;
use App\Models\Message;
use App\Traits\Chat\SetsParticipants;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MessageService
 * @package App\Chat\Services
 */
class MessageService
{
    use SetsParticipants;

    /**
     * @var string
     */
    protected string $type = 'text';

    /**
     * @var array
     */
    protected array $data = [];

    /**
     * @var string
     */
    protected string $body;

    /**
     * @var Message $newMessage
     */
    protected static Message $newMessage;

    /**
     * @var array
     */
    protected array $attachFiles;

    /**
     * MessageService constructor.
     * @param Message $message
     */
    public function __construct(protected Message $message) { }

    /**
     * @param $message
     * @return $this
     */
    public function setMessage($message): static {
        if (is_object($message)) {
            $this->message = $message;
        } else {
            $this->body = $message ?? '';
        }

        return $this;
    }

    /**
     * Set Message type.
     *
     * @param string type
     *
     * @return $this
     */
    public function type(string $type): static {
        $this->type = $type;

        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function data(array $data): static {
        $this->data = $data;

        return $this;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getById($id): mixed {
        return $this->message->findOrFail($id);
    }

    /**
     * Mark a message as read.
     *
     * @return void
     */
    public function markRead()
    {
        $this->message->markRead($this->participant);
    }

    /**
     * Deletes message.
     *
     * @return void
     */
    public function delete()
    {
        $this->message->trash($this->participant);
    }

    /**
     * Get count for unread messages.
     *
     * @return void
     */
    public function unreadCount()
    {
        return $this->message->unreadCount($this->participant);
    }

    /**
     * @return Message
     */
    public function toggleFlag(): Message {
        return $this->message->toggleFlag($this->participant);
    }

    /**
     * Sets participant.
     *
     * @param array $files
     * @return $this
     */
    public function attachFiles(array $files): self
    {
        $this->attachFiles = $files;

        return $this;
    }

    /**
     * Sets participant.
     *
     * @return array
     */
    public function getAttachedFiles(): array {
        return $this->attachFiles;
    }

    /**
     * @return Conversation
     */
    public function getConversation(): Conversation {
        return $this->conversation;
    }

    /**
     * @return Message
     */
    public static function getNewMessage(): Message {
        return static::$newMessage;
    }

    /**
     * @return bool
     */
    public function flagged(): bool {
        return $this->message->flagged($this->participant);
    }

    /**
     * @param $message
     * @return static
     */
    public function setCreatedMessage($message): static {
        static::$newMessage = $message;

        return $this;
    }

    /**
     * Sends the message.
     *
     * @throws Exception|\Throwable
     *
     * @return void
     */
    public function send() {

        if (!$this->sender) {
            throw new Exception('Message sender has not been set');
        }

        if (!$this->conversation) {
            throw new Exception('Message Conversation has not been set');
        }

        $conversation = $this->getConversation();

        $participant = $conversation->participantFromSender($this->sender);

        $newMessage = $this->message->send($conversation, $this->body, $participant, $this->getAttachedFiles());

        $this->setCreatedMessage($newMessage);
    }
}
