<?php

namespace App\Traits\Chat;

use App\Models\Conversation;
use Illuminate\Database\Eloquent\Model;

/**
 * Trait SetsParticipants
 * @package App\Traits\Chat
 */
trait SetsParticipants
{

    /**
     * @var Model
     */
    protected Model $sender;

    /**
     * @var Model
     */
    protected Model $recipient;

    /**
     * @var Model
     */
    protected Model $participant;

    /**
     * @var Model
     */
    protected Model $starter;

    /**
     * @var Conversation
     */
    protected Conversation $conversation;

    /**
     * Sets participant.
     *
     * @param Model $participant
     *
     * @return static
     */
    public function setParticipant(Model $participant): static
    {
        $this->participant = $participant;

        return $this;
    }

    /**
     * Sets participant.
     *
     * @param Model $starter
     *
     * @return static
     */
    public function setStarter(Model $starter): static
    {
        $this->starter = $starter;

        return $this;
    }

    /**
     * Sets the participant that's sending the message.
     *
     * @param Model $sender
     *
     * @return static
     */
    public function from(Model $sender): static
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * Sets the participant to receive the message.
     *
     * @param Model $recipient
     *
     * @return static
     */
    public function to(Model $recipient): static
    {
        $this->recipient = $recipient;

        return $this;
    }

    /**
     * Sets the participant to receive the message.
     *
     * @param Conversation $conversation
     * @return static
     */
    public function conversation(Conversation $conversation): static
    {
        $this->conversation = $conversation;

        return $this;
    }
}
