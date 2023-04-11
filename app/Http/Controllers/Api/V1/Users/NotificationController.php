<?php

namespace App\Http\Controllers\Api\V1\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Users\SendCodeAgainRequest;
use App\Http\Resources\V1\User\NotificationResource;
use App\Models\Notification;
use App\Repositories\V1\Users\NotificationRepository;
use App\Traits\ApiResponseAble;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ItemNotFoundException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class NotificationController
 * @package App\Http\Controllers\Api\V1\Users
 */
class NotificationController extends Controller {

    use ApiResponseAble;

    /**
     * AuthController constructor.
     * @param NotificationRepository $notificationRepository
     */
    public function __construct(private NotificationRepository $notificationRepository) { }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function list(Request $request): AnonymousResourceCollection {
        $results = $this->notificationRepository->getNotifications($request);

        return NotificationResource::collection($results);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function readNotifications(Request $request): JsonResponse {

        $request->validate([
            'ids' => 'array|required',
            'ids.*' => 'exists:notifications,id',
        ]);

        $this->notificationRepository->markAsRead($request->get('ids'));

        return $this->success('', __('messages.SUCCESS_OPERATED'));
    }

    /**
     * @param Notification $notification
     * @return JsonResponse
     */
    public function destroy(Notification $notification): JsonResponse {
        $notification->delete();
        return $this->success('', __('messages.ITEM_DELETED'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function checkConfirmationCode(Request $request): JsonResponse {

        $correct = $this->notificationRepository->checkConfirmationCode(
            $request->user(),
            $request->get('code')
        );

        if (!$correct) {
            return $this->error('Your Code is incorrect', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->success('', __('messages.SUCCESS_OPERATED'));
    }

    /**
     * Повторно отправляем код подтверждения
     *
     * @param SendCodeAgainRequest $request
     * @return JsonResponse
     */
    public function sendConfirmationCode(SendCodeAgainRequest $request): JsonResponse {
        try {
            $typeConfirm = $request->get('confirmation_type');

            $request->user()->notify(app($typeConfirm));

            return $this->success([], \Messages::generateConfirmMessages($typeConfirm));
        } catch (ItemNotFoundException | ModelNotFoundException $e) {
            return $this->error($e->getMessage(), Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }
}
