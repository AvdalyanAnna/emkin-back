<?php

namespace App\Http\Controllers\Api\V1\Admin\Commercial;

use App\Enums\Admin\Commercial\CommercialStatuses;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Admin\Commercial\CommercialUsersRequest;
use App\Http\Resources\V1\Admin\Commercial\CommercialUserResource;
use App\Models\CommercialUsers;
use App\Repositories\V1\Admin\Commercial\CommercialUserRepository;
use App\Traits\ApiResponseAble;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;

/**
 * Class UsersController
 * @package App\Http\Controllers\Api\V1\Users
 */
class UsersController extends Controller {

    use ApiResponseAble;

    /**
     * AuthController constructor.
     * @param CommercialUserRepository $commercialUserRepository
     */
    public function __construct(private CommercialUserRepository $commercialUserRepository) { }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function list(Request $request): AnonymousResourceCollection {
        $draft = CommercialStatuses::DRAFT;
        $active = CommercialStatuses::ACTIVE;
        $period = $request->query('period', false);

        return CommercialUserResource::collection(
            $this->commercialUserRepository->paginate($request->query('perPage'))
        )->additional([
            'counts' => \DB::table('commercial_users as cm')
                ->when($period, function ($q) use ($period) {
                    $q->where('period_of_stay_id', '=', $period);
                })
                ->selectRaw("count(CASE WHEN cm.status = $draft THEN 1 END) as draft_count")
                ->selectRaw("count(CASE WHEN cm.status = $active THEN 1 END) as active_count")
                ->first()
        ]);
    }

    /**
     * @param CommercialUsersRequest $request
     * @return CommercialUserResource|JsonResponse
     */
    public function updateOrCreate(CommercialUsersRequest $request): CommercialUserResource|JsonResponse {
        try {
            $commercial = $this->commercialUserRepository->updateOrCreateModel($request->all());

            return new CommercialUserResource($commercial->load(['avatar', 'period']));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }

    /**
     * @param CommercialUsers $commercialUser
     * @return JsonResponse
     */
    public function destroy(CommercialUsers $commercialUser): JsonResponse {
        try {
            $commercialUser->delete();

            return $this->success([], __('messages.ITEM_DELETED'));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }

    /**
     * @param CommercialUsers $commercialUser
     * @return JsonResponse
     */
    public function changeToDraft(CommercialUsers $commercialUser): JsonResponse {
        try {
            $commercialUser->update(['status' => CommercialStatuses::DRAFT]);

            return $this->success([], __('messages.ITEM_DELETED'));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }

    /**
     * @param CommercialUsers $commercialUser
     * @return CommercialUserResource|JsonResponse
     */
    public function details(CommercialUsers $commercialUser): CommercialUserResource|JsonResponse {
        return new CommercialUserResource($commercialUser->load(['avatar', 'period']));
    }
}
