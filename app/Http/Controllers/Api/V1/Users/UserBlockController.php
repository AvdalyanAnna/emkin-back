<?php

namespace App\Http\Controllers\Api\V1\Users;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\User\UserResource;
use App\Traits\ApiResponseAble;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Class UserBlockController
 * @package App\Http\Controllers\Api\V1\Users
 */
class UserBlockController extends Controller {

    use ApiResponseAble;

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function blockUser(Request $request): JsonResponse {

        $request->validate(['blocked_id' => 'required|exists:users,id']);

        if (!$request->user()->isBlocked($request->get('blocked_id'))) {
            $request->user()->block_list()->attach($request->get('blocked_id'));
        }

        return $this->success([], __('messages.SUCCESS_OPERATED'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function unBlockUser(Request $request): JsonResponse {

        $request->validate(['unblock_id' => 'required|exists:users,id']);

        if ($request->user()->isBlocked($request->get('unblock_id'))) {
            $request->user()->block_list()->detach($request->get('unblock_id'));
        }

        return $this->success([], __('messages.SUCCESS_OPERATED'));
    }

    /**
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function blockList(Request $request): AnonymousResourceCollection {
        $user = $request->user()->load('block_list.avatar');

        return UserResource::collection($user->block_list);
    }
}
