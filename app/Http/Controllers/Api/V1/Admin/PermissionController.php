<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Admin\Permissions\GiveRoleRequest;
use App\Http\Resources\V1\User\PermissionResource;
use App\Models\Permission;
use App\Repositories\V1\Users\UserRepositoryEloquent;
use App\Services\V1\Users\UserService;
use App\Traits\ApiResponseAble;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;

/**
 * Class PermissionController
 * @package App\Http\Controllers\Api\V1\Admin
 */
class PermissionController extends Controller {

    use ApiResponseAble;

    /**
     * AuthController constructor.
     * @param UserRepositoryEloquent $userRepository
     * @param UserService $userService
     */
    public function __construct(private UserRepositoryEloquent $userRepository, private UserService $userService) { }

    /**
     * @param GiveRoleRequest $request
     * @return JsonResponse
     */
    public function giveRole(GiveRoleRequest $request): JsonResponse {
        try {
            $this->userRepository->giveRoleUser($request->all());

            return $this->success([], __('messages.CHANGE_ROLE'));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }

    /**
     * @return AnonymousResourceCollection
     */
    public function getPermissions(): AnonymousResourceCollection {
        return PermissionResource::collection(Permission::all());
    }
}
