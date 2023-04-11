<?php

namespace App\Facades\V1;

use App\Enums\Chat\ChatServiceNamesEnum;
use App\Exceptions\BlockedUserException;
use App\Exceptions\Chat\DirectMessagingExistsException;
use App\Exceptions\Chat\IncorrectServiceInstanceException;
use App\Exceptions\Chat\InvalidDirectMessageNumberOfParticipants;
use App\Exceptions\Chat\ConversationIncorrectParticipantException;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageNotification;
use \App\Services\V1\Chat\ConversationService;
use \App\Services\V1\Chat\MessageService;
use App\Traits\Chat\SetsParticipants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ItemNotFoundException;

/**
 * Class Chat
 * @package App\Chat
 */
class Chat {

    use SetsParticipants;

    /**
     * The Singleton's instance is stored in a static field. This field is an
     * array, because we'll allow our Singleton to have subclasses. Each item in
     * this array will be an instance of a specific Singleton's subclass. You'll
     * see how this works in a moment.
     */
    private static array $instances = [];

    /**
     * This is the static method that controls the access to the singleton
     * instance. On the first run, it creates a singleton object and places it
     * into the static field. On subsequent runs, it returns the client existing
     * object stored in the static field.
     *
     * This implementation lets you subclass the Singleton class while keeping
     * just one instance of each subclass around.
     */
    public static function getInstance(): Chat {
        $cls = static::class;
        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = app(static::class);
        }

        return self::$instances[$cls];
    }

    /**
     * @param MessageService $messageService
     * @param ConversationService $conversationService
     * @param MessageNotification $messageNotification
     */
    public function __construct(
        protected MessageService $messageService,
        protected ConversationService $conversationService,
        protected MessageNotification $messageNotification
    ) { }

    /**
     * Creates a new conversation.
     *
     * @param array $participants
     * @param array $data
     * @param null $advertise_id
     * @param bool $started
     * @throws DirectMessagingExistsException
     * @throws InvalidDirectMessageNumberOfParticipants
     * @return Conversation
     */
    public function createConversation(array $participants, array $data = [], $advertise_id = null, bool $started = false): Conversation {
        $payload = [
            'participants' => $participants,
            'data' => $data,
            'direct_message' => $this->conversationService->directMessage,
        ];

        if ($advertise_id)
            $payload = array_merge($payload, ['advertise_id' => $advertise_id]);

        if ($started)
            $payload = array_merge($payload, compact('started'));

        if ($this->starter instanceof Model)
            $payload['starter_id'] = $this->starter->getKey();

        return $this->conversationService->start($payload);
    }

    /**
     * @return $this
     */
    public function makeDirect(): static {
        $this->conversationService->directMessage = true;

        return $this;
    }

    /**
     * Sets message.
     *
     * @param object|string|null $message
     *
     * @return MessageService
     */
    public function message(object|string $message = null): MessageService {
        return $this->messageService->setMessage($message);
    }

    /**
     * @param $conversation
     * @return bool
     */
    public function isFirstMessage($conversation): bool {
        return $conversation->messages()->count() === 1;
    }

    /**
     * @throws ConversationIncorrectParticipantException
     * @throws \Throwable
     * @return bool
     */
    public function throwIsIncorrectParticipant(): bool {
        $participants = $this->conversationService->getConversation()->getParticipants();
        $currentPartId = $this->participant->getKey();
        $currentParType = $this->participant->getMorphClass();
        $isParticipant = false;

        foreach ($participants as $participant) {
            $participant_id = $participant->getKey();

            $blockedFromAnother = in_array($currentPartId, $this->participant->blockedListFromAnotherUser());

            throw_if($blockedFromAnother, BlockedUserException::blockedUser(false));

            $blockedFromCurrent = in_array($participant_id, $this->participant->blockedListFromCurrentUser());

            throw_if($blockedFromCurrent, BlockedUserException::blockedUser());

            if ($participant->getKey() === $currentPartId && $participant->getMorphClass() === $currentParType) {
                $isParticipant = true;
            }
        }

        if (!$isParticipant) {
            throw ConversationIncorrectParticipantException::invalidParticipant($this->participant);
        }

        return true;
    }

    /**
     * @param Model $model
     * @param array $conversations
     * @return void
     */
    public function deleteConversations(Model $model, array $conversations): void {
        $conversations = $this->conversationService->getConversations($model, $conversations);

        $conversations->map(function ($conversation) use ($model) {

            /** @var Conversation $conversation */
            if ($conversation->isStarter($model)) {
                $conversation->markAsDeleteFromStarter($model);
            }

            if (!$conversation->isStarter($model)) {
                $conversation->markAsDeleteFromReceiver($model);
            }
        });
    }

    /**
     * @param Model $model
     * @param int $participantId
     * @param int $advertise_id
     * @return Conversation|bool
     */
    public function resetConversation(Model $model, int $participantId, int $advertise_id): Conversation|bool {
        $conversation = Conversation::query()
            ->where('starter_id', $model->getKey())
            ->whereHas('participants', function ($q) use ($participantId) {
                $q->where(['messageable_id' => $participantId])->withTrashed();
            })
            ->where('advertise_id', '=', $advertise_id)
            ->withTrashed()
            ->first();

        if (!$conversation) return false;

        return $conversation->resetAndRestore();
    }

    /**
     * Gets MessageService.
     *
     * @return Message
     */
    public function takeMessage(): Message {
        return $this->messageService->getNewMessage();
    }

    /**
     * Gets MessageService.
     *
     * @param array $files
     * @return Chat
     */
    public function attachFiles(array $files): static {

        $this->messageService->{__FUNCTION__}($files);

        return $this;
    }

    /**
     * Sets Conversation.
     *
     * @param Conversation $conversation
     *
     * @return static
     */
    public function conversation(Conversation $conversation): static {
        $this->messageService->{__FUNCTION__}($conversation);
        $this->conversationService->{__FUNCTION__}($conversation);

        return $this;
    }

    /**
     * Gets ConversationService.
     *
     * @param string $service
     * @throws IncorrectServiceInstanceException
     * @return ConversationService|MessageService
     */
    public function serviceInstances(string $service): ConversationService|MessageService {
        $chatServices = ChatServiceNamesEnum::getValues();

        if (!in_array($service, $chatServices)) {
            throw IncorrectServiceInstanceException::invalidService($service);
        }

        return $this->{$service};
    }

    /**
     * Get unread notifications.
     *
     * @return MessageNotification
     */
    public function unReadNotifications(): MessageNotification {
        return $this->messageNotification->unReadNotifications($this->participant);
    }

    /**
     * Should the messages be broadcasted.
     *
     * @param $participants
     * @param $id
     * @return bool
     */
    public static function takeAnotherParticipant($participants, $id): mixed {

        if(!$participant = $participants->where('id', '<>', $id)->first()) {
            throw new ItemNotFoundException('Participant not found');
        }

        return $participant;
    }

    /**
     * Should the messages be broadcasted.
     *
     * @return bool
     */
    public static function broadcasts(): bool {
        return config('chat.broadcasts');
    }
}
