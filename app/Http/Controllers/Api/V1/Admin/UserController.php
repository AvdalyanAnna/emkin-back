<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\AdvertiseResource;
use App\Http\Resources\V1\User\UserResource;
use App\Models\User;
use App\Repositories\V1\AdvertiseRepository;
use App\Repositories\V1\Users\UserRepository;
use App\Services\V1\Users\UserService;
use App\Traits\ApiResponseAble;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Class UserController
 * @package App\Http\Controllers\Api\V1\Users
 */
class UserController extends Controller {

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
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @return AnonymousResourceCollection|BinaryFileResponse
     */
    public function list(Request $request, UserService $userService): AnonymousResourceCollection|BinaryFileResponse {

        if ($request->query('export',false)) {
            $this->authorize('exportData', $request->user());
        }

        $collection = $userService
            ->setExportData($request->query('export',false))
            ->getUsers($request);

        if ($collection instanceof BinaryFileResponse) return $collection;

        return UserResource::collection($collection);
    }

    /**
     * @param Request $request
     * @param User $user
     * @param AdvertiseRepository $advertiseRepository
     * @return AnonymousResourceCollection
     */
    public function getAdvertises(
        Request $request,
        User $user,
        AdvertiseRepository $advertiseRepository
    ): AnonymousResourceCollection {
        $result = AdvertiseResource::collection(
            $advertiseRepository->where('user_id', $user->id)->paginate($request->query('per_page'))
        );

        if ($this->canShowInformation($request->user())) {
            $user->load('avatar');
            $user->loadCount(['advertises', 'canceled_advertises']);
            $result = $result->additional(['user' => UserResource::make($user)]);
        }

        return $result;
    }

    /**
     * @param $user
     * @return bool
     */
    public function canShowInformation($user): bool {
        return $user->can('user_information') || $user->isAdmin();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param UserService $userService
     * @return JsonResponse
     */
    public function banUser(Request $request, UserService $userService): JsonResponse {
        $request->validate([
            'banned_ids' => 'array|required',
            'banned_ids.*' => 'exists:users,id',
            'type' => 'required|numeric|between:0,1',
        ]);

        $userService->banUser($request, user()->id);

        return $this->success([],__('messages.SUCCESS_OPERATED'));
    }
}
