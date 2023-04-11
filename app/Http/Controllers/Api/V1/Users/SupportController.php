<?php

namespace App\Http\Controllers\Api\V1\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\SupportChat\AddTicketRequest;
use App\Http\Resources\V1\Admin\Support\ThemeResource;
use App\Http\Resources\V1\Admin\Support\TicketResource;
use App\Models\SupportTheme;
use App\Repositories\V1\Admin\Support\TicketRepository;
use App\Traits\ApiResponseAble;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Class SupportController
 * @package App\Http\Controllers\Api\V1\Users
 */
class SupportController extends Controller {

    use ApiResponseAble;

    /**
     * SupportController constructor.
     * @param TicketRepository $ticketRepository
     */
    public function __construct(private TicketRepository $ticketRepository) { }

    /**
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function getThemes(Request $request): AnonymousResourceCollection {
        return ThemeResource::collection(
            SupportTheme::paginate($request->query('perPage'))
        );
    }

    /**
     * @param AddTicketRequest $request
     * @throws \Throwable
     * @return JsonResponse|TicketResource
     */
    public function addTicket(AddTicketRequest $request): JsonResponse|TicketResource {
        try {
            if ($request->bearerToken() && $token = PersonalAccessToken::findToken($request->bearerToken())) {
                $request->request->set('user', $token->tokenable);
            }

            $ticket = $this->ticketRepository->addTicket($request->all(), $request->get('user'));

            return new TicketResource($ticket);
        } catch (\Exception $e) {
            \Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }
}
