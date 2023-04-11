<?php

namespace App\Http\Controllers\Api\V1\Users;

use App\Enums\Advertise\AdvertiseStatus;
use App\Enums\MediaCollections;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Users\ChangePasswordRequest;
use App\Http\Requests\V1\Users\UpdateAuthTypesRequest;
use App\Http\Requests\V1\Users\UpdateProfileRequest;
use App\Http\Resources\V1\AdvertiseResource;
use App\Http\Resources\V1\User\PublicUserResource;
use App\Http\Resources\V1\User\UserResource;
use App\Models\User;
use App\Repositories\V1\AdvertiseRepository;
use App\Repositories\V1\Users\UserRepository;
use App\Services\V1\Users\UserService;
use App\Traits\ApiResponseAble;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;

/**
 * Class UserController
 * @package App\Http\Controllers\Api\V1\Users
 */
class UserController extends Controller {

    use ApiResponseAble;

    /**
     * AuthController constructor.
     * @param UserRepository $userRepository
     * @param UserService $userService
     */
    public function __construct(private UserRepository $userRepository, private UserService $userService) { }

    /**
     * @param UpdateProfileRequest $request
     * @throws \Throwable
     * @return JsonResponse|UserResource
     */
    public function update(UpdateProfileRequest $request): JsonResponse|UserResource  {
        try {
            $this->authorize(__FUNCTION__, $request->user());

            $user = $this->userRepository->updateUserProfile($request->user(), $request->all());

            return new UserResource($user);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }

    /**
     * @param ChangePasswordRequest $request
     * @throws \Throwable
     * @return JsonResponse|UserResource
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse|UserResource  {
        try {
            $this->authorize(__FUNCTION__, [$request->user(), $request->get('code')]);

            $user = $this->userRepository->updateUserProfile(
                $request->user(),
                $request->all(),
                MediaCollections::USER_AVATAR_COLLECTION,
                true,
            );

            return new UserResource($user);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }

    /**
     * @param UpdateAuthTypesRequest $request
     * @return JsonResponse
     */
    public function sendCodForEmailOrPhone(UpdateAuthTypesRequest $request): JsonResponse {
        $this->userService->sendCodForEmailOrPhone($request);

        return $this->success('', __('messages.SUCCESS_OPERATED'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getStatisticUser(Request $request): JsonResponse {
        $statistics = $this->userRepository->getStatistic(
            $request->user()->id,
            $request->query('status')
        );

        return $this->success([
            'statistics' => $statistics
        ], __('messages.SUCCESS_OPERATED'));
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @param AdvertiseRepository $advertiseRepository
     * @return AnonymousResourceCollection
     */
    public function getAdvertises(Request $request, AdvertiseRepository $advertiseRepository): AnonymousResourceCollection {
        return AdvertiseResource::collection(
            $advertiseRepository
            ->where('user_id', $request->user()->id)
            ->paginate($request->query('per_page'))
        );
    }


    /**
     * @param Request $request
     * @param User $user
     * @param AdvertiseRepository $advertiseRepository
     * @return AnonymousResourceCollection|JsonResponse
     */
    public function getUserInformation(
        Request $request,
        User $user,
        AdvertiseRepository $advertiseRepository
    ): AnonymousResourceCollection|JsonResponse {
        $result = AdvertiseResource::collection(
            $advertiseRepository->where('user_id', $user->id)->paginate($request->query('per_page'))
        );
        $active = AdvertiseStatus::Active;
        $inactive = AdvertiseStatus::InActive;

        $user->load('avatar');
        $user->loadCount(['advertises', 'canceled_advertises']);

        return $result->additional([
            'user' => PublicUserResource::make($user),
            'counts' => \DB::table('advertises as adv')
                ->where('user_id', '=', $user->id)
                ->selectRaw("count(CASE WHEN adv.status = $active THEN 1 END) as active_count")
                ->selectRaw("count(CASE WHEN adv.status = $inactive THEN 1 END) as inactive_count")
                ->first(),
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getFavoriteAdvertises(Request $request): JsonResponse {
        $user = $request->user()->load('advertise_favorites');
        $ids = $user->advertise_favorites->pluck('id')->toArray();
        return $this->success(['favorites' => $ids]);
    }



    /**
     * @param UpdateAuthTypesRequest $request
     * @throws \Throwable
     * @return JsonResponse|UserResource
     */
    public function changeEmailOrPhone(UpdateAuthTypesRequest $request): JsonResponse|UserResource  {
        try {
            $this->authorize('changePassword', [$request->user(), $request->get('code')]);

            $user = $this->userRepository->updateUserProfile(
                $request->user(),
                $request->all(),
                MediaCollections::USER_AVATAR_COLLECTION,
                true
            );

            return new UserResource($user);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }
}
