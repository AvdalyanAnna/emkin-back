<?php

namespace App\Repositories\V1\Chat;

use App\Models\Message;

/**
 * Class MessageRepositoryEloquent
 * @package App\Repositories\V1\Chat
 */
class MessageRepositoryEloquent implements MessageRepository {

    /**
     * @return string
     */
    public function takeModel(): string {
        return Message::class;
    }

    /**
     * @param $messageId
     * @param $authUserId
     * @return bool
     */
    public function softDeleteMessage($messageId, $authUserId): bool {
        $message = $this->with(['conversation' => function ($q) use ($authUserId) {
            $q->where('user_one', $authUserId);
            $q->orWhere('user_two', $authUserId);
        }])->find($messageId);

        if (is_null($message->conversation)) {
            return false;
        }

        if ($message->user_id == $authUserId) {
            $message->deleted_from_sender = 1;
        } else {
            $message->deleted_from_receiver = 1;
        }

        return (boolean) $message->update();
    }
}
