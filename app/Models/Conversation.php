<?php

namespace App\Models;

use App\Events\Chat\ConversationMessageReadAll;
use App\Traits\ActiveGlobalScopeAble;
use App\Traits\CascadeSoftRestores;
use App\Traits\TimezoneChangeAble;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use App\Chat\ConfigurationManager;
use App\Exceptions\Chat\DirectMessagingExistsException;
use App\Exceptions\Chat\InvalidDirectMessageNumberOfParticipants;

/**
 * Class Conversation
 *
 * @package App\Models
 * @property int $id
 * @property bool $private
 * @property bool $direct_message
 * @property array|null $data
 * @property int $support
 * @property int|null $ticket_id
 * @property int|null $advertise_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Message|null $last_message
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Message[] $messages
 * @property-read int|null $messages_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Participation[] $participants
 * @property-read int|null $participants_count
 * @property-read \App\Models\Ticket|null $ticket
 * @method static Conversation newModelQuery()
 * @method static Conversation newQuery()
 * @method static Conversation query()
 * @method static Conversation whereAdvertiseId($value)
 * @method static Conversation whereCreatedAt($value)
 * @method static Conversation whereData($value)
 * @method static Conversation whereDirectMessage($value)
 * @method static Conversation whereId($value)
 * @method static Conversation wherePrivate($value)
 * @method static Conversation whereSupport($value)
 * @method static Conversation whereTicketId($value)
 * @method static Conversation whereUpdatedAt($value)
 * @property int $started
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Advertise|null $advertise
 * @method static Builder|Conversation onlyTrashed()
 * @method static Conversation whereDeletedAt($value)
 * @method static Conversation whereStarted($value)
 * @method static Builder|Conversation withTrashed()
 * @method static Builder|Conversation withoutTrashed()
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MessageNotification[] $unread_messages
 * @property-read int|null $unread_messages_count
 * @property int|null $starter_id
 * @property-read \App\Models\User|null $starter
 * @method static Conversation whereStarterId($value)
 * @method static Conversation issetParticipation(\Illuminate\Database\Eloquent\Model $model)
 * @property int $deleted_from_starter
 * @property int $deleted_from_receiver
 * @method static Conversation whereDeletedFromReceiver($value)
 * @method static Conversation whereDeletedFromStarter($value)
 * @method static Conversation starterNoDeleted(\Illuminate\Database\Eloquent\Model $model)
 * @method static Conversation orReceiverNoDeleted(\Illuminate\Database\Eloquent\Model $model)
 * @method static Conversation hasFilter(array $data)
 * @method static Conversation isBuying(\Illuminate\Database\Eloquent\Model $model, string $conType)
 * @method static Conversation isChat(\Illuminate\Database\Eloquent\Model $model, string $conType)
 * @method static Conversation isSupport(\Illuminate\Database\Eloquent\Model $model, string $conType)
 * @method static \Illuminate\Database\Eloquent\Builder|Conversation isResell(\Illuminate\Database\Eloquent\Model $model, string $conType)
 * @method static \Illuminate\Database\Eloquent\Builder|Conversation isSearch(\Illuminate\Database\Eloquent\Model $model, ?string $search = null)
 * @property int $started_event_calling
 * @method static \Illuminate\Database\Eloquent\Builder|Conversation whereStartedEventCalling($value)
 * @property int $status
 * @method static \Illuminate\Database\Eloquent\Builder|Conversation whereStatus($value)
 * @mixin \Eloquent
 */
class Conversation extends BaseModel
{
    use CascadeSoftRestores, SoftDeletes, CascadeSoftDeletes, TimezoneChangeAble;

    /**
     * @var string
     */
    protected $table = ConfigurationManager::CONVERSATIONS_TABLE;

    /**
     * @var array
     */
    protected array $cascadeDeletes = ['participants', 'messages'];

    /**
     * @var array
     */
    protected array $cascadeRestore = ['participants'];

    /**
     * @var array
     */
    public static array $selectedRelationsConversation = [
        'ticket:id,status',
        'advertise:id,name,slug',
        'participants:conversation_id,messageable_id,messageable_type',
        'participants.messageable:id,first_name,last_name',
        'participants.messageable.avatar:id,model_id,model_type,disk,conversions_disk,collection_name,custom_properties,file_name'
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'data' => 'array',
        'direct_message' => 'boolean',
        'private' => 'boolean',
    ];

    /**
     * @var string[]
     */
    protected $fillable = [
        'data',
        'direct_message',
        'ticket_id',
        'status',
        'advertise_id',
        'started',
        'deleted_from_starter',
        'deleted_from_receiver',
        'starter_id',
        'deleted_at'
    ];

    /**
     * Conversation participants.
     *
     * @return HasMany
     */
    public function participants(): HasMany {
        return $this
            ->hasMany(Participation::class,'conversation_id')
            ->where('moderator_changed', '=', false);
    }

    /**
     * @return BelongsTo
     */
    public function ticket(): BelongsTo {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * @return BelongsTo
     */
    public function advertise(): BelongsTo {
        return $this->belongsTo(Advertise::class);
    }

    /**
     * @return BelongsTo
     */
    public function starter(): BelongsTo {
        return $this->belongsTo(User::class,'starter_id');
    }

    /**
     * @return Collection
     */
    public function getParticipants(): Collection {
        return $this->participants()->get()->pluck('messageable');
    }

    /**
     * Return the recent message in a Conversation.
     *
     * @return HasOne
     */
    public function last_message(): HasOne {
        return $this->hasOne(Message::class)
            ->orderBy("{$this->tablePrefix}messages.id", 'desc')
            /*->with('participation')*/;
    }

    /**
     * Messages in conversation.
     *
     * @return HasMany
     */
    public function messages(): HasMany {
        return $this->hasMany(Message::class, 'conversation_id'); //->with('sender');
    }

    /**
     * Get messages for a conversation.
     *
     * @param Model $participant
     * @param array $paginationParams
     * @param bool $deleted
     * @return mixed
     */
    public function getMessages(Model $participant, $paginationParams, $deleted = false): mixed {
        return $this->getConversationMessages($participant, $paginationParams, $deleted);
    }

    /**
     * @param $participant
     * @param array $options
     * @return mixed
     */
    public function getParticipantConversations($participant, array $options): mixed {
        return $this->getConversationsList($participant, $options);
    }

    /**
     * @param Model $sender
     * @return mixed
     */
    public function participantFromSender(Model $sender): mixed
    {
        return $this->participants()->where([
            'conversation_id'  => $this->getKey(),
            'messageable_id'   => $sender->getKey(),
            'messageable_type' => $sender->getMorphClass(),
        ])->first();
    }

    /**
     * Add user to conversation.
     *
     * @param array $participants
     * @return Conversation
     */
    public function addParticipants(array $participants): static {
        foreach ($participants as $participant) $participant->joinConversation($this);

        return $this;
    }

    /**
     * Remove participant from conversation.
     *
     * @param $participants
     *
     * @return Conversation
     */
    public function removeParticipant($participants): static {
        if (is_array($participants)) {
            foreach($participants as $participant) $participant->leaveConversation($this->getKey());

//            event(new ParticipantsLeft($this, $participants));

            return $this;
        }

        $participants->leaveConversation($this->getKey());
        $this->clearConversation($participants);

//        event(new ParticipantsLeft($this, [$participants]));

        return $this;
    }

    /**
     * @return $this
     */
    public function makeStarted(): static {
        $this->started = true;
        $this->save();
        return $this;
    }

    /**
     * Starts a new conversation.
     *
     * @param array $payload
     *
     * @throws DirectMessagingExistsException
     * @throws InvalidDirectMessageNumberOfParticipants
     *
     * @return Conversation
     */
    public function start(array $payload): self
    {
        if ($payload['direct_message']) {
            if (count($payload['participants']) > 2)
                throw new InvalidDirectMessageNumberOfParticipants();

            $this->ensureNoDirectMessagingExist($payload['participants']);
        }

        /** @var Conversation $conversation */

        $createData = [
            'data' => $payload['data'],
            'started' => $payload['started'] ?? false,
            'starter_id' => $payload['starter_id'],
            'direct_message' => (bool) $payload['direct_message'],
        ];

        if (isset($payload['advertise_id'])) $createData['advertise_id'] = $payload['advertise_id'];

        $conversation = $this->create($createData);

        if ($payload['participants']) $conversation->addParticipants($payload['participants']);

        return $conversation->fresh();
    }

    /**
     * Sets conversation as public or private.
     *
     * @param bool $isPrivate
     *
     * @return Conversation
     */
    public function makePrivate(bool $isPrivate = true): static {
        $this->private = $isPrivate;

        $this->save();

        return $this;
    }

    /**
     * Sets conversation as direct message.
     *
     * @param bool $isDirect
     *
     * @throws InvalidDirectMessageNumberOfParticipants
     * @throws DirectMessagingExistsException
     *
     * @return Conversation
     */
    public function makeDirect(bool $isDirect = true): static {
        if ($this->participants()->count() > 2) {
            throw new InvalidDirectMessageNumberOfParticipants();
        }

        $participants = $this->participants()->get()->pluck('messageable');

        $this->ensureNoDirectMessagingExist($participants);

        $this->direct_message = $isDirect;
        $this->save();

        return $this;
    }

    /**
     * @param $participants
     *
     * @throws DirectMessagingExistsException
     */
    private function ensureNoDirectMessagingExist($participants)
    {
        /** @var Conversation $common */
        $common = \Chat::conversations()->between($participants[0], $participants[1]);

        if (!is_null($common)) {
            throw new DirectMessagingExistsException();
        }
    }

    /**
     * Gets conversations for a specific participant.
     *
     * @param Model $participant
     * @param bool  $isDirectMessage
     *
     * @return Collection
     */
    public function participantConversations(Model $participant, bool $isDirectMessage = false): Collection
    {
        $conversations = $participant->participation->pluck('conversation');

        return $isDirectMessage ? $conversations->where('direct_message', 1) : $conversations;
    }

    /**
     * Get unread notifications.
     *
     * @param Model $participant
     *
     * @return Collection
     */
    public function unReadNotifications(Model $participant): Collection
    {
        $notifications = MessageNotification::where([
            ['messageable_id', '=', $participant->getKey()],
            ['messageable_type', '=', $participant->getMorphClass()],
            ['conversation_id', '=', $this->id],
            ['is_seen', '=', 0],
        ])->get();

        return $notifications;
    }

    /**
     * Gets the notifications for the participant.
     *
     * @param      $participant
     * @param bool $readAll
     *
     * @return mixed
     */
    public function getNotifications($participant, bool $readAll = false): mixed {
        return $this->notifications($participant, $readAll);
    }

    /**
     * Clears participant conversation.
     *
     * @param $participant
     *
     * @return void
     */
    public function clear($participant): void
    {
        $this->clearConversation($participant);

//        if ($this->unDeletedCount() === 0) {
//            event(new AllParticipantsClearedConversation($this));
//        }
    }

    /**
     * Marks all the messages in a conversation as read for the participant.
     *
     * @param Model $participant
     *
     * @return void
     */
    public function readAll(Model $participant): void
    {
        $this->getNotifications($participant, true);
    }

    /**
     * Get messages in conversation for the specific participant.
     *
     * @param Model $participant
     * @param $paginationParams
     * @param $deleted
     *
     * @return mixed
     */
    private function getConversationMessages(Model $participant, $paginationParams, $deleted): mixed {
        $mess = $this->tablePrefix . 'messages';
        $notify = $this->tablePrefix . 'message_notifications';
        $dateKayMessages = \DB::raw('DATE_FORMAT(chat_messages.created_at, "%Y %M %d") as date');
        $messageCreatedTime = \DB::raw('DATE_FORMAT(chat_messages.created_at, "%h:%i %p") as send_time');

        $messages = $this->messages()
            ->select(
                $dateKayMessages, "$mess.id", "$mess.body", "$mess.conversation_id", "$mess.participation_id", "$mess.created_at",
                "$mess.updated_at", "$notify.updated_at as read_at", $messageCreatedTime, "$notify.is_sender", "$notify.is_seen"
            )
            ->with(Message::$selectedRelationsConversation)
            ->whereHas('notifications', function ($q) use ($notify, $participant) {
                $q->where([
                    ["$notify.deleted_at", '=', null],
                    ["$notify.messageable_id", '=', $participant->getKey()],
                    ["$notify.messageable_type", '=', $participant->getMorphClass()]
                ]);
            })
            ->join($notify, "$notify.message_id", '=', "$mess.id")
            ->where([
                ["$notify.messageable_id", '<>', $participant->getKey()],
                ["$notify.messageable_type", '=', $participant->getMorphClass()]
            ])
            ->orderBy("$mess.created_at", $paginationParams['sorting']);

        return $messages->paginate(
            $paginationParams['perPage'],
            ['*'],
            $paginationParams['pageName'],
            $paginationParams['page']
        );
    }

    /**
     * @param Model $participant
     * @param $options
     *
     * @return LengthAwarePaginator
     */
    private function getConversationsList(Model $participant, $options): LengthAwarePaginator {
        $prefix = $this->tablePrefix;
        $relations = array_merge(static::$selectedRelationsConversation, [
            'last_message' => function ($query) use ($participant, $prefix) {
                $query->join($prefix . 'message_notifications', $prefix . 'message_notifications.message_id', '=', $prefix . 'messages.id')
                    ->select($prefix . 'message_notifications.*', $prefix . 'messages.*')
                    ->where($prefix . 'message_notifications.messageable_id', '<>', $participant->getKey())
                    ->where($prefix . 'message_notifications.messageable_type', $participant->getMorphClass())
                    ->whereNull($prefix . 'message_notifications.deleted_at');
            }
        ]);

        return static::query()
            ->issetParticipation($participant)
            ->hasFilter(compact(['participant', 'options']))
            ->with($relations)
            ->select(['id', 'ticket_id', 'advertise_id', 'updated_at', 'status'])
            ->withCount(['unread_messages' => function($q) use ($participant) {
                $q->join('chat_participation as part', function ($q) use ($participant) {
                    $q->on('part.id', '=', 'chat_message_notifications.participation_id')
                    ->where('part.messageable_id', '=', $participant->getKey());
                });
            }])
            ->orderBy("updated_at", 'DESC')
            ->distinct('id')
            ->paginate($options['perPage'], ['*'], $options['pageName'], $options['page']);
    }

    /**
     * @return mixed
     */
    public function unDeletedCount(): mixed {
        return MessageNotification::where('conversation_id', $this->getKey())->count();
    }

    /**
     * @param Model $participant
     * @param $readAll
     * @return mixed
     */
    private function notifications(Model $participant, $readAll): mixed {
        $notifications = MessageNotification::query()
          ->where('conversation_id', $this->id)
          ->where('is_seen', '=', false)
          ->whereNull('deleted_at');

        if ($readAll) {

            $updated = $notifications->update(['is_seen' => 1]);

            if ($updated) {
                broadcast(ConversationMessageReadAll::make($this, $participant->getKey()))->toOthers();
            }

            return $updated;
        }

        return $notifications->get();
    }

    /**
     * @param $participant
     */
    private function clearConversation($participant): void
    {
        MessageNotification::where('messageable_id', $participant->getKey())
            ->where($this->tablePrefix . 'message_notifications.messageable_type', $participant->getMorphClass())
            ->where('conversation_id', $this->getKey())
            ->delete();
    }

    /**
     * @return HasMany
     */
    public function unread_messages(): HasMany {
        return $this
            ->hasMany(MessageNotification::class, 'conversation_id')
            ->where('is_seen', '=', false);
    }

    /**
     * @return bool
     */
    public function isDirectMessage(): bool
    {
        return $this->direct_message;
    }

    /**
     * @return $this
     */
    public function reset(): static {
        $this->deleted_from_starter = false;
        $this->deleted_from_receiver = false;
        $this->started = false;
        $this->status = true;
        $this::unsetEventDispatcher();
        $this->save();

        return $this;
    }

    /**
     * @return $this
     */
    public function resetAndRestore(): static {
        $this->deleted_from_starter = false;
        $this->deleted_from_receiver = false;
        $this->started = false;
        $this->status = true;
        $this::unsetEventDispatcher();

        $this->restore();
        return $this;
    }

    /**
     * @param Model $model
     * @return bool
     */
    public function markAsDeleteFromReceiver(Model $model): bool {
        $this->deleted_from_receiver = true;
        static::unsetEventDispatcher();
        $this->save();
        $this->clear($model);
        return true;
    }

    /**
     * @param Model $model
     * @return bool
     */
    public function markAsDeleteFromStarter(Model $model): bool {
        $this->deleted_from_starter = true;
        static::unsetEventDispatcher();
        $this->save();
        $this->clear($model);
        return true;
    }

    /**
     * @return bool
     */
    public function isDeleteFromReceiver(): bool {
        return (boolean)$this->deleted_from_receiver;
    }

    /**
     * @return bool
     */
    public function isDeleteFromStarter(): bool {
        return (boolean)$this->deleted_from_starter;
    }

    /**
     * @param Model $model
     * @return bool
     */
    public function isStarter(Model $model): bool {
        return $model->getKey() === $this->starter_id;
    }

    /**
     * Scope a query to only include users of a given type.
     *
     * @param $query
     * @param array $data
     * @return mixed
     */
    public function scopeHasFilter($query, array $data): mixed {
        $filters = $data['options']['filters'];
        $search = $filters['search'] ?? null;
        $conType = $filters['conversation_type'] ?? null;
        $participant = $data['participant'];
        return $query->when(is_string($filters['conversation_type']), function ($q) use (
            $participant,
            $conType,
            $search,
            $filters
        ) {
            $q->when($conType === 'resell', function ($q) {
                $q->where('started', '<>', false);
            })->isSupport($participant, $conType, $filters)
                ->isChat($participant, $conType)
                ->isBuying($participant, $conType)
                ->isResell($participant, $conType)
                ->isSearch($participant, $search);
        });
    }

    /**
     * Scope a query to only include users of a given type.
     *
     * @param $query
     * @param Model $model
     * @return mixed
     */
    public function scopeIssetParticipation($query, Model $model): mixed {
        return
            $query
            ->whereHas('participants', function ($q) use ($model) {
                $q->where(['messageable_id' => $model->getKey()], ['messageable_type' => $model->getMorphClass()]);
            });
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeIsActive($query): mixed {
        return $query->where('status', '=', true);
    }

    /**
     * Scope a query to only include users of a given type.
     *
     * @param $query
     * @param Model $model
     * @param string $conType
     * @param array|null $filters
     * @return mixed
     */
    public function scopeIsSupport($query, Model $model, string $conType, array $filters = null): mixed {
        $isSupport = $conType === 'support';
        return $query->when($isSupport, function ($when) use ($model, $filters) {
            $when->when(isset($filters['theme_id']) && $filters['theme_id'], function ($q) use ($filters) {
                    $q->whereHas('ticket', function($q) use ($filters) {
                        $q->where('support_theme_id', '=', $filters['theme_id'])
                            ->when(isset($filters['ticket_status']), function ($q) use ($filters) {
                                $q->where('status', '=', $filters['ticket_status']);
                            });
                    });
                })
                ->where(function ($q) use ($model) {
                    $q->orWhere([
                        ['starter_id', '=', $model->getKey()],
                        ['deleted_from_starter', '=', false],
                    ])->orWhere([
                        ['starter_id', '<>', $model->getKey()],
                        ['deleted_from_receiver', '=', false],
                    ]);
                })->whereNotNull('ticket_id');
        });
    }

    /**
     * Scope a query to only include users of a given type.
     *
     * @param $query
     * @param Model $model
     * @param string $conType
     * @return mixed
     */
    public function scopeIsBuying($query, Model $model, string $conType): mixed {
        $isBuying = $conType === 'buying';
        return $query->when($isBuying, function ($when) use ($model) {
            $when->whereHas('advertise', function ($q) use ($model) {
                $q->where('user_id', '<>', $model->getKey());
            });
        });
    }

    /**
     * Scope a query to only include users of a given type.
     *
     * @param $query
     * @param Model $model
     * @param string $conType
     * @return mixed
     */
    public function scopeIsResell($query, Model $model, string $conType): mixed {
        $isResell = $conType === 'resell';
        return $query->when($isResell, function ($when) use ($model) {
            $when->whereHas('advertise', function ($q) use ($model) {
                $q->where('user_id', '=', $model->getKey());
            });
        });
    }

    /**
     * Scope a query to only include users of a given type.
     *
     * @param $query
     * @param Model $model
     * @param string $conType
     * @return mixed
     */
    public function scopeIsChat($query, Model $model, string $conType): mixed {
        $isChat = $conType !== 'support';
        return $query->when($isChat, function ($q) use ($model) {
            $q->whereNull('ticket_id')->where(function ($q) use ($model) {
                $q->where(function ($q) use ($model) {
                   $q->orWhere([
                       ['starter_id', '=', $model->getKey()],
                       ['deleted_from_starter', '=', false],
                   ])->orWhere([
                       ['starter_id', '<>', $model->getKey()],
                       ['deleted_from_receiver', '=', false],
                   ]);
                });
            });
        });
    }

    /**
     * Scope a query to only include users of a given type.
     *
     * @param $query
     * @param Model $model
     * @param string|null $search
     * @return mixed
     */
    public function scopeIsSearch($query, Model $model, string $search = null): mixed {
        return $query->when($search, function ($q) use ($model, $search) {
            $q->where(function ($q) use ($search) {
                $q->whereHas('advertise', function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%$search%");
                })->orWhereHas('participants.messageable', function ($q) use ($search) {
                    $q->where(function ($q) use ($search) {
                        $q->where('first_name', 'LIKE', "%$search%")->orWhere('last_name', 'LIKE', "%$search%");
                    });
                });
            });
        });
    }
}
