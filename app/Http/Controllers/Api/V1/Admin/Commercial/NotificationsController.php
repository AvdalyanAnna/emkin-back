<?php

namespace App\Http\Controllers\Api\V1\Admin\Commercial;

use App\Enums\Admin\Commercial\CommercialStatuses;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Admin\Commercial\NotificationRequest;
use App\Http\Resources\V1\Admin\Commercial\NotificationResource;
use App\Models\CommercialNotification;
use App\Repositories\V1\Admin\Commercial\CommercialNotificationRepository;
use App\Services\V1\Admin\CommercialService;
use App\Traits\ApiResponseAble;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;

/**
 * Class BusinessController
 * @package App\Http\Controllers\Api\V1\Admin\Commercial
 */
class NotificationsController extends Controller {

    use ApiResponseAble;

    /**
     * AuthController constructor.
     * @param CommercialNotificationRepository $commercialNotificationRepository
     */
    public function __construct(private CommercialNotificationRepository $commercialNotificationRepository) { }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function list(Request $request): AnonymousResourceCollection {
        $active = CommercialStatuses::ACTIVE;
        $draft = CommercialStatuses::DRAFT;

        return NotificationResource::collection(
            $this->commercialNotificationRepository->paginate($request->query('per_page'))
        )->additional([
            'counts' => \DB::table('commercial_notifications as cn')
                ->selectRaw("count(CASE WHEN cn.status = $active THEN 1 END) as active_count")
                ->selectRaw("count(CASE WHEN cn.status = $draft THEN 1 END) as draft_count")
                ->first()
        ]);
    }

    /**
     * @param NotificationRequest $request
     * @param CommercialService $commercialService
     * @throws \Throwable
     * @return NotificationResource|JsonResponse
     */
    public function updateOrCreate(NotificationRequest $request, CommercialService $commercialService): NotificationResource|JsonResponse {
        try {
            $notification = $this->commercialNotificationRepository->updateOrCreateModel($request->all());

            if ($request->get('public', false)) $commercialService->sendNotification($notification);

            return new NotificationResource($notification->load(['banner_image']));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }

    /**
     * @param CommercialNotification $commercialNotification
     * @return JsonResponse
     */
    public function destroy(CommercialNotification $commercialNotification): JsonResponse {
        try {
            $commercialNotification->delete();

            return $this->success([], __('messages.ITEM_DELETED'));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }

    /**
     * @param CommercialNotification $commercialNotification
     * @return JsonResponse
     */
    public function changeToDraft(CommercialNotification $commercialNotification): JsonResponse {
        try {
            $commercialNotification->update(['status' => CommercialStatuses::DRAFT]);

            return $this->success([], __('messages.ITEM_DELETED'));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }

    /**
     * @param CommercialNotification $commercialNotification
     * @return JsonResponse|NotificationResource
     */
    public function details(CommercialNotification $commercialNotification): JsonResponse|NotificationResource {
        return new NotificationResource($commercialNotification->load(['banner_image']));
    }
}
