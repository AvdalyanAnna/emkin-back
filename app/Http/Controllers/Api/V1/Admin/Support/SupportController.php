<?php

namespace App\Http\Controllers\Api\V1\Admin\Support;

use App\Criteria\V1\SearchCriteria;
use App\Enums\Users\TicketStatuses;
use App\Events\Chat\ConversationUpdated;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Admin\Support\ThemeResource;
use App\Http\Resources\V1\Admin\Support\TicketResource;
use App\Models\SupportTheme;
use App\Models\Ticket;
use App\Services\V1\Admin\SupportService;
use App\Traits\ApiResponseAble;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use phpDocumentor\Reflection\Types\False_;

/**
 * Class SupportController
 * @package App\Http\Controllers\Api\V1\Users
 */
class SupportController extends Controller {

    use ApiResponseAble;

    /**
     * SupportController constructor.
     * @param SupportService $supportService
     */
    public function __construct(private SupportService $supportService) { }

    /**
     * @return AnonymousResourceCollection
     */
    public function listThemes(): AnonymousResourceCollection {
        $collection = SupportTheme::where('status', '=', 1)
            ->orderBy('order','ASC')
            ->get();

        return ThemeResource::collection($collection);
    }

    /**
     * TODO Изменинть реализацию этого финкции тут много запросов
     * https://admin.emkin.com/support-chat
     *
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function listTickets(Request $request) {
        $close = TicketStatuses::CLOSE;
        $new = TicketStatuses::NEW;
        $viewed = TicketStatuses::VIEWED;
        $expectation = TicketStatuses::EXPECTATION;
        $isModerator = user()->hasRole(config('roles.roles.moderator.name'));
        $isAdmin = user()->hasRole(config('roles.roles.admin.name'));
        $status = false;
        $searchParams = $request->query('search', false);

        if ($searchParams) {
            $searchData = app(SearchCriteria::class)->parserSearchData($searchParams);
            $status = isset($searchData['status']) ? +$searchData['status'] : false;
        } else {
            $request->request->set('search', 'support_theme_id:1');
        }

        $moderatorId = user()->id;

        $data['tickets_statuses_counts'] = \DB::table('tickets as t')
            ->when($isModerator, function ($q) use ($new, $viewed, $expectation, $close, $moderatorId) {
                $q->selectRaw("count(CASE WHEN t.status = $new AND t.moderator_id IS NULL THEN 1 END) as new_count")
                    ->selectRaw("count(CASE WHEN t.status = $viewed AND t.moderator_id = $moderatorId THEN 1 END) as viewed_count")
                    ->selectRaw("count(CASE WHEN t.status = $expectation AND t.moderator_id = $moderatorId THEN 1 END) as expectation_count")
                    ->selectRaw("count(CASE WHEN t.status = $close AND t.moderator_id = $moderatorId THEN 1 END) as close_count");
            })
            ->when($isAdmin, function ($q) use ($new, $viewed, $expectation, $close, $moderatorId) {
                $q->where('moderator_id', '=', user()->id)
                    ->selectRaw("count(CASE WHEN t.status = $new THEN 1 END) as new_count")
                    ->selectRaw("count(CASE WHEN t.status = $viewed THEN 1 END) as viewed_count")
                    ->selectRaw("count(CASE WHEN t.status = $expectation THEN 1 END) as expectation_count")
                    ->selectRaw("count(CASE WHEN t.status = $close THEN 1 END) as close_count");
            })
            ->first();

        $data['themes'] = ThemeResource::collection(
            SupportTheme::withCount(['tickets' => function ($q) use ($status, $isModerator) {
                $q->when($isModerator && $status !== TicketStatuses::NEW, function ($q) {
                    $q->where('moderator_id', '=', user()->id);
                })->where('status', '=', $status);
            }])->get()
        );

        return TicketResource::collection(
            $this->supportService->ticket()->paginate($request->get('perPage'))
        )->additional($data);
    }

    /**
     * Действия для модераторов
     *
     * @param Ticket $ticket
     * @throws \Throwable
     * @return JsonResponse|TicketResource
     */
    public function acceptTicket(Ticket $ticket): JsonResponse|TicketResource {
        try {
            $message = __('messages.TICKET_ALREADY_ACCEPTED');

            if (!$ticket->moderator()->exists()) {
                $message = __('messages.TICKET_ACCEPTED');
                $ticket = $this->supportService->ticket()->acceptTicket(user(), $ticket);
            }

            return $this->success(['ticket' => new TicketResource($ticket)], $message, 'accepted');
        } catch (\Exception $e) {
            \Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }

    /**
     * Действия для модераторов
     *
     * @param Ticket $ticket
     * @param string $action (expect or close)
     * @return JsonResponse|TicketResource
     */
    public function actionTicket(Ticket $ticket, string $action): JsonResponse|TicketResource {
        try {
            if (!$ticket->moderator_id) {
                $this->error(__('messages.UNPROCESSABLE'),'forbidden');
            }

            if($action === 'close') {
                $message = __('messages.TICKET_CLOSED');
            } else {
                $message = __('messages.SUCCESS_OPERATED');
            }

            $this->supportService->ticket()->{$action . 'Ticket'}($ticket);

            if ($action === 'close') {
                broadcast(ConversationUpdated::make($ticket->conversation, user()->id))->toOthers();
            }

            return $this->success('ok', $message);
        } catch (\Exception $e) {
            \Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }
}
