<?php

namespace App\Traits\Chat;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Exceptions\Chat\InvalidDirectMessageNumberOfParticipants;
use App\Models\Conversation;
use App\Models\Participation;

/**
 * Trait Messageable
 * @package App\Traits\Chat
 */
trait Messageable
{
    public function conversations()
    {
        return $this->participation->pluck('conversation');
    }

    /**
     * @return MorphMany
     */
    public function participation(): MorphMany
    {
        return $this->morphMany(Participation::class, 'messageable');
    }

    /**
     * @param Conversation $conversation
     * @throws InvalidDirectMessageNumberOfParticipants
     */
    public function joinConversation(Conversation $conversation)
    {
        if ($conversation->isDirectMessage() && $conversation->participants()->count() == 2) {
            throw new InvalidDirectMessageNumberOfParticipants();
        }

        $participation = new Participation([
            'messageable_id'   => $this->getKey(),
            'messageable_type' => $this->getMorphClass(),
            'conversation_id'  => $conversation->getKey(),
        ]);

        $this->participation()->save($participation);
    }

    public function leaveConversation($conversationId)
    {
        $this->participation()->where([
            'messageable_id'   => $this->getKey(),
            'messageable_type' => $this->getMorphClass(),
            'conversation_id'  => $conversationId,
        ])->delete();
    }
}
