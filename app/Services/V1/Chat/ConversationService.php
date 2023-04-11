<?php

namespace App\Services\V1\Chat;

use App\Chat\ConfigurationManager;
use App\Enums\Admin\Moderator\ModeratorStatisticsEnum;
use App\Enums\Chat\ChatServiceNamesEnum;
use App\Enums\MediaCollections;
use App\Enums\Users\TicketStatuses;
use App\Events\Chat\ConversationUpdated;
use App\Exceptions\Chat\DirectMessagingExistsException;
use App\Exceptions\Chat\InvalidDirectMessageNumberOfParticipants;
use App\Facades\V1\Chat;
use App\Models\Complaint;
use App\Models\ModeratorStatistic;
use App\Models\User;
use App\Traits\UploadAble;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorAlias;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use App\Models\Conversation;
use App\Traits\Chat\Paginates;
use App\Traits\Chat\SetsParticipants;

/**
 * Class ConversationService
 * @package App\Chat\Services
 */
class ConversationService
{
    use SetsParticipants, Paginates, UploadAble;

    /**
     * @var array
     */
    protected array $filters = [];

    /**
     * @var bool
     */
    public bool $directMessage = false;

    /**
     * ConversationService constructor.
     * @param Conversation $conversation
     */
    public function __construct(protected Conversation $conversation) { }

    /**
     * @param array $payload
     * @throws DirectMessagingExistsException
     * @throws InvalidDirectMessageNumberOfParticipants
     * @return Conversation
     */
    public function start(array $payload): Conversation {
        return $this->conversation->start($payload);
    }

    /**
     * @param array $filters
     * @return $this
     */
    public function setFilters(array $filters): static {
        $this->filters = $filters;

        return $this;
    }

    /**
     * @param $type
     * @return $this
     */
    public function setConversationTypeFilters($type): static {
        $this->filters['conversation_type'] = $type;

        return $this;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getById($id): mixed {
        return $this->conversation->find($id);
    }

    /**
     * @return mixed
     */
    public function starter(): mixed {
        return $this->starter;
    }

    /**
     * Get messages in a conversation.
     */
    public function getMessages(): LengthAwarePaginatorAlias|HasMany|Builder {

        $messages = $this->conversation->getMessages($this->participant, $this->getPaginationParams(), $this->deleted);

        $itemsTransformed = $messages->getCollection()->groupBy('date');

        return new LengthAwarePaginator(
            $itemsTransformed,
            $messages->total(),
            $messages->perPage(),
            $messages->currentPage(), [
                'path' => \Request::url(),
                'query' => [
                    'page' => $messages->currentPage()
                ]
            ]
        );
    }

    /**
     * Clears conversation.
     */
    public function clear()
    {
        $this->conversation->clear($this->participant);
    }

    /**
     * @return Conversation
     */
    public function getConversation(): Conversation {
        return $this->conversation;
    }

    /**
     * @param array $attributes
     * @throws \Throwable
     * @return void
     */
    public function addComplaint(array $attributes): void {
        \DB::transaction(function () use ($attributes, &$complaint) {
            $complaint = Complaint::create($attributes);

            if (existsUploadAbleFileInArray($attributes))
                foreach ($attributes['files'] as $file)
                    $this->upload($complaint, $file, MediaCollections::CHAT_COMPLAINT_FILE);
        });
    }

    /**
     * Mark all messages in Conversation as read.
     *
     * @return void
     */
    public function readAll()
    {
        $this->conversation->readAll($this->participant);
    }

    /**
     * Get Private Conversation between two users.
     *
     * @param Model $participantOne
     * @param Model $participantTwo
     *
     * @return Conversation|null
     */
    public function between(Model $participantOne, Model $participantTwo): ?Conversation {
        $participantOneConversationIds = $this->conversation
            ->participantConversations($participantOne, true)
            ->pluck('id');

        $participantTwoConversationIds = $this->conversation
            ->participantConversations($participantTwo, true)
            ->pluck('id');

        $common = $this->getConversationsInCommon($participantOneConversationIds, $participantTwoConversationIds);

        return $common ? $this->conversation->findOrFail($common[0]) : null;
    }

    /**
     * Get Conversations with latest message.
     *
     * @return LengthAwarePaginator
     */
    public function get(): LengthAwarePaginator {
        return $this->conversation->getParticipantConversations($this->participant, [
            'perPage'   => $this->perPage,
            'page'      => $this->page,
            'pageName'  => 'page',
            'filters'   => $this->filters,
        ]);
    }

    /**
     * Get Conversations with latest message.
     *
     * @param $participant
     * @param array $conversations_ids
     * @return mixed
     */
    public function getConversations($participant, array $conversations_ids = []): mixed {
        return $this->conversation
            ->issetParticipation($participant)
            ->whereIn('id', $conversations_ids)
            ->get();
    }

    /**
     * Add user(s) to a conversation.
     *
     * @param array $participants
     *
     * @return Conversation
     */
    public function addParticipants(array $participants): Conversation {
        return $this->conversation->addParticipants($participants);
    }

    /**
     * Remove user(s) from a conversation.
     *
     * @param $users / array of user ids or an integer
     *
     * @return Conversation
     */
    public function removeParticipants($users): Conversation {
        return $this->conversation->removeParticipant($users);
    }

    /**
     * @param $oldModerator
     * @param $newModerator
     * @throws \Throwable
     * @return Chat
     */
    public function changeModerator($oldModerator, $newModerator): Chat {

        // todo разделить на маленких функции

        \DB::transaction(function () use ($newModerator, $oldModerator) {
            $ticket = $this->conversation->ticket()->first();
            $ticket_id = $ticket->id;
            $ticket->update([
                'moderator_id' => $newModerator->id,
                'status' => TicketStatuses::EXPECTATION
            ]);

            $oldParticipant = $this->conversation
                ->participants()
                ->where('messageable_id', '=', $oldModerator->id)
                ->first();

            $oldParticipant_id = $oldParticipant->id;

            $oldParticipant->update(['moderator_changed' => true]);

            $this->conversation->update(['starter_id' => $newModerator->id]);
            $this->conversation->addParticipants([$newModerator]);

            \DB::table('chat_message_notifications')
                ->where([
                    ['conversation_id', '=', $this->conversation->id],
                    ['participation_id', '=', $oldParticipant_id],
                ])
                ->update(['deleted_at' => now()]);

            /**
             * Сохраняем Статистику (Кол-во передевенных запросов поддержки на дургого менеджера)
             */
            ModeratorStatistic::where([
                ['ticket_id', '=', $ticket_id],
                ['moderator_id', '=', $oldModerator->id],
                ['type', '=', ModeratorStatisticsEnum::PENDING_TICKETS],
            ])->update(['type' => ModeratorStatisticsEnum::REQUEST_TO_ANOTHER_MANAGER]);
        });

        return \Chat::getInstance();
    }

    /**
     * @param User $user
     * @throws \Throwable
     * @return Chat
     */
    public function closeConversation(User $user): Chat {

        \DB::transaction(function () use ($user) {
            \Chat::getInstance()
                ->conversation($this->conversation)
                ->serviceInstances(ChatServiceNamesEnum::CONVERSATIONS_SERVICE)
                ->setParticipant($user)
                ->readAll();

            $this->conversation->update(['status' => 0]);
        });

        if ($user->hasRole(config('roles.roles.user.name'))) {
            broadcast(ConversationUpdated::make($this->conversation))->toOthers();
        }

        return \Chat::getInstance();
    }

    /**
     * Возвращает количество комнат по типу (есть 3 типа buying,resell,support)
     * и возвращает все количество непрочитанных сообщении
     *
     * @param User $user
     * @return array
     */
    public function getConversationsCountsByTypesAndUnreadMessagesCount(User $user): array {
        $PART = ConfigurationManager::PARTICIPATION_TABLE;
        $CONV = ConfigurationManager::CONVERSATIONS_TABLE;
        $NOTI = ConfigurationManager::MESSAGE_NOTIFICATIONS_TABLE;
        $MESS = ConfigurationManager::MESSAGES_TABLE;

        $allQuery = \DB::table("$PART as PART")
            ->join("$CONV as CONV", function($join) {
                $join
                    ->on('PART.conversation_id', '=', 'CONV.id')
                    ->where('started', '<>', false);
            })
            ->leftJoin("advertises as ADVE",'CONV.advertise_id', '=', 'ADVE.id')
            ->groupBy('current_user')
            ->where([
                'PART.messageable_id' => $user->getKey(),
                'PART.messageable_type' => $user->getMorphClass(),
            ]);

        $conversations = $allQuery
            ->select('PART.messageable_id as current_user')
            ->selectRaw('count(CASE WHEN CONV.ticket_id IS NOT NULL THEN 1 END) as support_count')
            ->selectRaw("count(CASE WHEN CONV.ticket_id IS NULL AND ADVE.user_id = {$user->getKey()} THEN 1 END) as resell_count")
            ->selectRaw("count(CASE WHEN CONV.ticket_id IS NULL AND ADVE.user_id != {$user->getKey()} THEN 1 END) as buying_count")
            ->first();

        $unreadNotifications = $allQuery
            ->leftJoin("$NOTI as NOTI", function($join) use ($MESS) {
                $join
                    ->on('PART.messageable_id', '=', 'NOTI.messageable_id')
                    ->whereColumn('PART.messageable_type', 'NOTI.messageable_type')
                    ->whereColumn('CONV.id', 'NOTI.conversation_id')
                    ->where(['NOTI.is_seen' => false, 'NOTI.is_sender' => false])
                    ->where('CONV.started', '=', true)
                    ->join("$MESS as MESS", function($join) {
                        $join->on('NOTI.message_id', '=', 'MESS.id')->whereNull('MESS.deleted_at');
                    });
            })
            ->select('PART.messageable_id as current_user', \DB::raw('COUNT(NOTI.id) as unread_count'))
            ->first();


        unset($conversations->current_user);

        if($unreadNotifications && property_exists($unreadNotifications, 'unread_count')) {
            $conversations->unread_count = $unreadNotifications->unread_count;
        }

        return (array)$conversations;
    }

    /**
     * Возвращает все непрочитанные сообщение пользователя
     *
     * @param User $user
     * @param int|null $conversation_id
     * @return int
     */
    public function getUnreadMessageNotificationsCount(User $user, ?int $conversation_id = null): int {

        $conditions = [
            'messageable_id'   => $user->getKey(),
            'messageable_type' => $user->getMorphClass(),
            'is_seen' => false,
            'is_sender' => false,
        ];

        if ($conversation_id) {
            $conditions = array_merge($conditions, compact('conversation_id'));
        }

        return \DB::table(ConfigurationManager::MESSAGE_NOTIFICATIONS_TABLE)
            ->where($conditions)
            ->count();
    }

    /**
     * Get count for unread messages.
     *
     * @return int
     */
    public function unreadCount(): int {
        return $this->conversation->unReadNotifications($this->participant)->count();
    }

    /**
     * Gets the conversations in common.
     *
     * @param Collection $conversation1 The conversation Ids for user one
     * @param Collection $conversation2 The conversation Ids for user two
     *
     * @return array The conversations in common.
     */
    private function getConversationsInCommon(Collection $conversation1, Collection $conversation2): array {
        return array_values(array_intersect($conversation1->toArray(), $conversation2->toArray()));
    }

    /**
     * Sets the conversation type to query for, public or private.
     *
     * @param bool $isPrivate
     *
     * @return $this
     */
    public function isPrivate(bool $isPrivate = true): static {
        $this->filters['private'] = $isPrivate;

        return $this;
    }

    /**
     * Sets the conversation type to query for, public or private.
     *
     * @return bool
     */
    public function filterExistsUser(): bool {
        return $this->filters['user'] instanceof User;
    }

    /**
     * Sets the conversation type to query for direct conversations.
     *
     * @param bool $isDirectMessage
     *
     * @return $this
     */
    public function isDirect(bool $isDirectMessage = true): static {
        $this->filters['direct_message'] = $isDirectMessage;

        // Direct messages are always private
        $this->filters['private'] = true;

        return $this;
    }

    /**
     * @param null $participant
     * @return mixed
     */
    public function getParticipation($participant = null): mixed {
        $participant = $participant ?? $this->participant;

        return $participant->participation()->first();
    }
}
