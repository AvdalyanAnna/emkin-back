<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\Admin\Moderator\ModeratorStatisticsEnum;
use App\Enums\MediaCollections;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Admin\Permissions\GivePermissionRequest;
use App\Http\Requests\V1\Admin\Users\ModeratorUpdateRequest;
use App\Http\Resources\V1\User\UserResource;
use App\Models\User;
use App\Repositories\V1\Users\UserRepository;
use App\Services\V1\Users\UserService;
use App\Traits\ApiResponseAble;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Class UserController
 * @package App\Http\Controllers\Api\V1\Users
 */
class ModeratorController extends Controller {

    use ApiResponseAble;

    /**
     * AuthController constructor.
     * @param UserRepository $userRepository
     */
    public function __construct(private UserRepository $userRepository) { }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @param UserService $userService
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @return AnonymousResourceCollection|BinaryFileResponse
     */
    public function list(Request $request, UserService $userService): AnonymousResourceCollection|BinaryFileResponse {

        $collection = $userService
            ->setExportData($request->query('export',false))
            ->getUsers($request, true);

        if ($collection instanceof BinaryFileResponse) return $collection;

        return UserResource::collection($collection);
    }

    /**
     * @param User $user
     * @return UserResource
     */
    public function getModerator(User $user): UserResource {
        return new UserResource($user->load(['roles','avatar','permissions']));
    }

    /**
     * @param Request $request
     * @param User $moderator
     * @return JsonResponse
     */
    public function getStatistics(Request $request, User $moderator): JsonResponse {
        $viewed = ModeratorStatisticsEnum::VIEWED_ADS;
        $approved = ModeratorStatisticsEnum::APPROVED_ADS;
        $rejected = ModeratorStatisticsEnum::REJECTED_ADS;
        $banned = ModeratorStatisticsEnum::BANNED_USERS;
        $unBanned = ModeratorStatisticsEnum::UNBANNED_USERS;
        $closedTickets = ModeratorStatisticsEnum::CLOSED_TICKETS;
        $pendingTickets = ModeratorStatisticsEnum::PENDING_TICKETS;
        $requestAnotherModerator = ModeratorStatisticsEnum::REQUEST_TO_ANOTHER_MANAGER;
//        $hangs_without_activity_hors = \Carbon\Carbon::now()->subHours(24)->toDateTimeString();

        $statistics = \DB::table('moderator_statistics as ms')
            ->where('moderator_id', '=', $moderator->id)
            ->selectRaw("count(CASE WHEN ms.type = $viewed THEN 1 END) as viewed_count")
            ->selectRaw("count(CASE WHEN ms.type = $approved THEN 1 END) as approved_count")
            ->selectRaw("count(CASE WHEN ms.type = $rejected THEN 1 END) as rejected_count")
            ->selectRaw("count(CASE WHEN ms.type = $banned THEN 1 END) as banned_count")
            ->selectRaw("count(CASE WHEN ms.type = $unBanned THEN 1 END) as un_banned_count")
            ->selectRaw("count(CASE WHEN ms.type = $closedTickets THEN 1 END) as closed_tickets_count")
            ->selectRaw("count(CASE WHEN ms.type = $pendingTickets THEN 1 END) as pending_tickets_count")
            ->selectRaw("count(CASE WHEN ms.type = $requestAnotherModerator THEN 1 END) as another_moderator_count")
//            ->selectRaw("count(CASE WHEN ms.type = $approved AND DATE_FORMAT(ms.created_at, '%Y-%m-%d %H:%i') <= $hangs_without_activity_hors THEN 1 END) as hangs_without_activity_count")
            ->first();

        return $this->success($statistics);
    }

    /**
     * @param GivePermissionRequest $request
     * @return JsonResponse
     */
    public function givePermission(GivePermissionRequest $request): JsonResponse {
        try {
            $this->userRepository->givePermissionUser($request->all());

            return $this->success([], __('messages.CHANGE_PERMISSION'));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }

    /**
     * @param ModeratorUpdateRequest $request
     * @param UserService $userService
     * @return JsonResponse|UserResource
     */
    public function store(ModeratorUpdateRequest $request, UserService $userService): JsonResponse|UserResource {
        try {
            $data = array_merge($request->all(), ['verified_at' => now()]);

            $moderator = $userService->addModerator($data);

            return new UserResource($moderator->load(['avatar']));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }

    /**
     * @param ModeratorUpdateRequest $request
     * @throws \Throwable
     * @return JsonResponse|UserResource
     */
    public function update(ModeratorUpdateRequest $request): JsonResponse|UserResource {
        try {
            $moderator = $this->userRepository->find($request->get('id'));
            config(['audit.events' => ['created', 'updated']]);

            $moderator = $this->userRepository->updateUserProfile(
                $moderator,
                $request->all(),
                MediaCollections::MODERATOR_USER_AVATAR_COLLECTION,
                true
            );

            return new UserResource($moderator->load(['avatar']));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }

    /**
     * @param User $user
     * @return JsonResponse
     */
    public function destroy(User $user): JsonResponse {
        try {
            $user->delete();

            return $this->success([], __('messages.ITEM_DELETED'));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function banModerator(Request $request): JsonResponse {

        $request->validate([
            'banned_ids' => 'array|required',
            'banned_ids.*' => 'exists:users,id',
            'type' => 'required|numeric|between:0,1',
        ]);

        DB::table('users')->whereIn('id', $request->get('banned_ids'))->update(['banned' => +$request->get('type')]);

        return $this->success([],__('messages.SUCCESS_OPERATED'));
    }
}
