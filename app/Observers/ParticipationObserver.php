<?php

namespace App\Observers;

use App\Events\Chat\ParticipantsJoined;
use App\Models\Participation;

/**
 * Class ParticipationObserver
 * @package App\Observers
 */
class ParticipationObserver
{
    /**
     * Handle the Participation "created" event.
     *
     * @param Participation $participation
     * @return void
     */
    public function created(Participation $participation)
    {
//        if ($this->enabledBroadcasts() && $this->canBeEventingConversationToReceiver($participation)) {
//            $participation->load(['conversation','messageable']);
//            $conversation = $participation->conversation;
//            $user = $participation->messageable;
//
//            broadcast(new ParticipantsJoined($conversation, $user->id));
//        }
    }

    /**
     * Handle the Participation "updated" event.
     *
     * @param Participation $participation
     * @return void
     */
    public function updated(Participation $participation)
    {
        //
    }

    /**
     * Handle the Participation "deleted" event.
     *
     * @param Participation $participation
     * @return void
     */
    public function deleted(Participation $participation)
    {
        //
    }

    /**
     * Handle the Participation "restored" event.
     *
     * @param Participation $participation
     * @return void
     */
    public function restored(Participation $participation)
    {
        //
    }

    /**
     * Handle the Participation "force deleted" event.
     *
     * @param Participation $participation
     * @return void
     */
    public function forceDeleted(Participation $participation)
    {
        //
    }

    /**
     * @param Participation $participation
     * @return bool
     */
    protected function canBeEventingConversationToReceiver(Participation $participation): bool {
        $conversation = $participation->conversation;
        $user = $participation->messageable;

        return ($conversation->started && $user->id !== $conversation->starter_id);
    }

    /**
     * @return bool
     */
    public function enabledBroadcasts(): bool {
        return \Chat::getInstance()->broadcasts();
    }
}
