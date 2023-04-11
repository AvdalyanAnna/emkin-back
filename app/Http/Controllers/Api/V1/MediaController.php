<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CityResource;
use App\Http\Resources\V1\StateResource;
use App\Models\Media;
use App\Repositories\V1\CityRepository;
use App\Repositories\V1\CityRepositoryEloquent;
use App\Repositories\V1\StateRepositoryEloquent;
use App\Traits\ApiResponseAble;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Class MediaController
 * @package App\Http\Controllers\Api\V1
 */
class MediaController extends Controller {

    use ApiResponseAble;

    /**
     * @param StateRepositoryEloquent $stateRepository
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Media $media): \Illuminate\Http\JsonResponse {
        $this->authorize(__FUNCTION__, $media);

        return $this->success('ok', __('messages.ITEM_DELETED'));
    }
}
