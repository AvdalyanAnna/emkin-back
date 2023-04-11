<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('send-message.{conversationId}', function ($user, $conversation_id) {
    return $user->existsConversation($conversation_id);
});

Broadcast::channel('chat-conversation-updated.{user_id}', function ($user, $user_id) {
    return (int) $user->id === (int) $user_id;
});

Broadcast::channel('chat-conversation-unread-messages-count.{user_id}', function ($user, $user_id) {
    return (int) $user->id === (int) $user_id;
});

Broadcast::channel('chat-conversation-readAt-all.{conversationId}-{user_id}', function ($user, $conversation_id, $user_id) {
    return $user->existsConversation($conversation_id) && (int)$user->id === (int) $user_id;
});
