<?php

namespace App\Repositories\V1\Chat;

use App\Models\Conversation;

/**
 * Class ConversationRepository
 * @package App\Repositories\V1\Users
 */
class ConversationRepositoryEloquent {

    /**
     * @return string
     */
    public function takeModel(): string {
        return Conversation::class;
    }

    /**
     * retrieve all message thread without soft deleted message with latest one message and
     * sender and receiver user model
     *
     * @param int $user
     * @param $order
     * @param int $offset
     * @param int $take
     * @return  \Illuminate\Support\Collection
     */
    public function threads($user, $order, $offset, $take): \Illuminate\Support\Collection {
        $conv = new Conversation();
        $conv->authUser = $user;
        $msgThread = $conv->with(
            [
                'messages' => function ($q) use ($user) {
                    return $q->where(
                        function ($q) use ($user) {
                            $q->where('user_id', $user)
                                ->where('deleted_from_sender', 0);
                        }
                    )
                        ->orWhere(
                            function ($q) use ($user) {
                                $q->where('user_id', '!=', $user);
                                $q->where('deleted_from_receiver', 0);
                            }
                        )
                        ->latest();
                }, 'messages.sender', 'userone', 'usertwo'
            ]
        )
            ->withCount([
                'messages as unread_messages' => function ($q) use ($user) {
                    $q->where('user_id', '<>', $user);
                    $q->where('deleted_from_receiver', 0);
                    $q->where('is_seen', 0);
                }
            ])
            ->where('user_one', $user)
            ->orWhere('user_two', $user)
            ->offset($offset)
            ->take($take)
            ->orderBy('updated_at', $order)
            ->get();

        $threads = [];

        foreach ($msgThread as $thread) {
            $collection = (object)null;
            $conversationWith = ($thread->userone->id == $user) ? $thread->usertwo : $thread->userone;
            $collection->thread = $thread->messages->first();
            $collection->withUser = $conversationWith;
            $collection->unread_messages = $thread->unread_messages;
            $threads[] = $collection;
        }

        return collect($threads);
    }
}
