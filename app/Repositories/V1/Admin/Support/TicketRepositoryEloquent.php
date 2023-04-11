<?php

namespace App\Repositories\V1\Admin\Support;

use App\Criteria\V1\SearchCriteria;
use App\Enums\Admin\Moderator\ModeratorStatisticsEnum;
use App\Enums\MediaCollections;
use App\Enums\Users\TicketStatuses;
use App\Events\Chat\ConversationUpdated;
use App\Events\Chat\MessageUnreadCounts;
use App\Mail\V1\Support\AcceptedTicketEmail;
use App\Mail\V1\Support\AddTicketEmail;
use App\Models\Conversation;
use App\Models\ModeratorStatistic;
use App\Models\SupportTheme;
use App\Models\Ticket;
use App\Models\User;
use App\Repositories\V1\Base\BaseRepository;
use Illuminate\Support\Facades\DB;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class TicketRepositoryEloquent
 * @package App\Repositories\V1\Users
 */
class TicketRepositoryEloquent extends BaseRepository implements TicketRepository
{
    use \App\Traits\UploadAble;

    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name' => 'LIKE',
        'description' => 'LIKE',
        'email' => 'LIKE',
        'user.first_name' => 'LIKE',
        'user.last_name' => 'LIKE',
        'moderator.first_name' => 'LIKE',
        'moderator.last_name' => 'LIKE',
        'created_at' => 'between',
        'support_theme_id',
        'moderator_id',
        'user.email',
        'moderator.email',
        'status',
    ];

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model(): string {
        return Ticket::class;
    }

    /**
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function boot() {
        $this->pushCriteria(app(SearchCriteria::class));
    }

    /**
     * @param $attributes
     * @param User|null $user
     * @throws \Throwable
     * @return mixed
     */
    public function addTicket($attributes, ?User $user): Ticket {

        if ($user instanceof User) {
            $attributes['user_id'] = $user->id;
        }

        \DB::transaction(function () use ($attributes, &$ticket, $user) {

            $ticket = parent::create($attributes);

            if (existsUploadAbleFileInArray($attributes))
                foreach ($attributes['files'] as $file)
                    $this->upload($ticket, $file, MediaCollections::SUPPORT_TICKET_FILE);

            if ($this->isContactUs($attributes))
                $this->getFilesAndSendEmailTicket($ticket, $attributes);

            if ($this->isSupportAndNoAuthUser($user, $attributes)) {
                $attributes['theme'] = SupportTheme::find($attributes['support_theme_id'])->title;
                $this->getFilesAndSendEmailTicket($ticket, $attributes);
            }
        });

        return $ticket;
    }

    /**
     * @param User $moderator
     * @param Ticket $ticket
     * @throws \Throwable
     * @return mixed
     */
    public function acceptTicket(User $moderator, Ticket $ticket): Ticket {
        $conversation = false;
        \DB::transaction(function () use ($moderator, &$ticket, &$conversation) {
            $this->expectTicket($ticket, $moderator->id);

            if ($user = $ticket->user) {

                Conversation::unsetEventDispatcher();

                \Chat::getInstance()
                    ->setStarter(user())
                    ->createConversation(
                        [$user, user()], [], null, true
                    )
                    ->ticket()
                    ->associate($ticket)
                    ->save();

                $conversation = Conversation::where([
                    ['ticket_id', '=', $ticket->id],
                    ['starter_id', '=', $moderator->id],
                ])->first();

                \Chat::getInstance()
                    ->conversation($conversation)
                    ->message('Hello i am moderator')
                    ->attachFiles([])
                    ->from($moderator)
                    ->send();
            } else {
                \Mail::to($ticket->email)->send(new AcceptedTicketEmail($ticket->name));
            }

            /**
             * Сохраняем Статистику (Кол-во не завершенных запросов (билети) в поддержке)
             */
            ModeratorStatistic::make([
                'ticket_id' => $ticket->id,
                'moderator_id' => $moderator->id,
                'type' => ModeratorStatisticsEnum::PENDING_TICKETS,
            ]);
        });

        if ($conversation) {
            broadcast(ConversationUpdated::make($conversation))->toOthers();
            broadcast(MessageUnreadCounts::make($conversation, user()->id))->toOthers();
        }

        return $ticket->fresh()->load(['user', 'conversation', 'moderator']);
    }

    /**
     * @param Ticket $ticket
     * @return Ticket
     *@throws ValidatorException|\Throwable
     */
    public function closeTicket(Ticket $ticket): Ticket {
        DB::transaction(function () use ($ticket, &$ticketUpdated) {
            $ticketUpdated = parent::update([
                'status' => TicketStatuses::CLOSE,
            ], $ticket->id);

            /**
             * Сохраняем Статистику (Кол-во завершенных запросов в поддержке)
             */
            if ($ticketUpdated) {
                if (!$ticketUpdated->user_id) {
                    ModeratorStatistic::make([
                        'moderator_id' => $ticket->moderator_id ?? \user()->id,
                        'type' => ModeratorStatisticsEnum::CLOSED_TICKETS,
                    ]);
                } else {
                    ModeratorStatistic::where([
                        ['ticket_id', '=', $ticket->id],
                        ['moderator_id', '=', $ticket->moderator_id],
                        ['type', '=', ModeratorStatisticsEnum::PENDING_TICKETS],
                    ])->update([
                        'moderator_id' => $ticket->moderator_id,
                        'type' => ModeratorStatisticsEnum::CLOSED_TICKETS,
                    ]);
                }

                $ticket->conversation->update(['status' => 0]);
            }
        });

        return $ticketUpdated;
    }

    /**
     * @param Ticket $ticket
     * @throws ValidatorException
     * @return Ticket
     */
    public function viewedTicket(Ticket $ticket): Ticket {
        return parent::update([
            'status' => TicketStatuses::VIEWED,
        ], $ticket->id);
    }

    /**
     * @param Ticket $ticket
     * @param int|null $moderator_id
     * @return Ticket
     * @throws ValidatorException
     */
    public function expectTicket(Ticket $ticket, ?int $moderator_id = null): Ticket {

        $data = [
          'status' => TicketStatuses::EXPECTATION,
          'updated_at' => now(),
        ];

        if ($moderator_id) {
            $data['moderator_id'] = $moderator_id;
        }

        return parent::update($data, $ticket->id);
    }

    /**
     * @param Ticket $ticket
     * @param array $attributes
     */
    public function getFilesAndSendEmailTicket(Ticket $ticket, array $attributes): void {
        $ticket->load('files');

        if ($files = $ticket->files) $attributes['files'] = $files;

        \Mail::to(config('app.corporate_mail'))->send(new AddTicketEmail($attributes));
    }

    /**
     *
     * Проверяет если Нет Пользователя И ест support_theme_id значит Кто то
     * добавляет билет надо отправить форму на ем аил
     *
     * @param $user
     * @param array $attributes
     * @return bool
     */
    public function isSupportAndNoAuthUser($user, array $attributes): bool {
        return !$user instanceof User && !$this->isContactUs($attributes);
    }

    /**
     * Проверяет если support_theme_id не существует значит работала контактная форма
     * и надо по любому отправить почту
     *
     * @param array $attributes
     * @return bool
     */
    public function isContactUs(array $attributes): bool {
        return !isset($attributes['support_theme_id']);
    }
}
