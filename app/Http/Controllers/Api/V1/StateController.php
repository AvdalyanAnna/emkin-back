<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CityResource;
use App\Http\Resources\V1\StateResource;
use App\Repositories\V1\CityRepository;
use App\Repositories\V1\CityRepositoryEloquent;
use App\Repositories\V1\StateRepositoryEloquent;
use App\Traits\ApiResponseAble;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Class StateController
 * @package App\Http\Controllers\Api\V1
 */
class StateController extends Controller {

    use ApiResponseAble;

    /**
     * StateController constructor.
     * @param CityRepository $cityRepository
     */
    public function __construct(private CityRepository $cityRepository) { }

    /**
     * @param StateRepositoryEloquent $stateRepository
     * @return AnonymousResourceCollection
     */
    public function getStates(StateRepositoryEloquent $stateRepository): AnonymousResourceCollection {
        $states = $stateRepository->get();
        return StateResource::collection($states);
    }

    /**
     * @param Request $request
     * @param CityRepositoryEloquent $cityRepository
     * @return AnonymousResourceCollection
     */
    public function getCitiesBySearch(Request $request): AnonymousResourceCollection {
        return CityResource::collection(
            $this->cityRepository->paginate($request->query('per_page'))
        );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function city(Request $request): \Illuminate\Http\JsonResponse {
        $city = $this->cityRepository->findCityByLatLongAndCityName($request->all());

        return $this->success(['city' => isset($city) && is_object($city) ? $city : null]);
    }
}
