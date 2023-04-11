<?php

namespace App\Http\Controllers\Api\V1;

use App\Criteria\V1\Advertises\AdvertiseByCategoryWhereIn;
use App\Criteria\V1\Advertises\AdvertiseFilters;
use App\Criteria\V1\Users\BannedUserCriteria;
use App\Enums\Advertise\AdvertiseStatistic;
use App\Enums\Advertise\AdvertiseStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\AdvertiseRequest;
use App\Http\Requests\V1\AdvertisesRequest;
use App\Http\Resources\V1\Admin\Category\CategoryResource;
use App\Http\Resources\V1\AdvertiseResource;
use App\Models\Advertise;
use App\Models\Media;
use App\Repositories\V1\Admin\Category\CategoryRepository;
use App\Repositories\V1\AdvertiseRepository;
use App\Traits\ApiResponseAble;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;

/**
 * Class AdvertiseController
 * @package App\Http\Controllers\Api\V1\Admin\Category
 */
class AdvertiseController extends Controller {

    use ApiResponseAble;

    /**
     * AdvertiseController constructor.
     * @param AdvertiseRepository $advertiseRepository
     */
    public function __construct(private AdvertiseRepository $advertiseRepository) { }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function list(Request $request): AnonymousResourceCollection {
        /**
         * Сохраняем Строку Поиска которую ввел пользователь или обновляем ее
         */
        $this->advertiseRepository->updateOrCreateSearchText($request->query('search'));

        $this->advertiseRepository->pushCriteria(BannedUserCriteria::class);

        if ($request->query('category_id', false)) {
            $this->advertiseRepository->pushCriteria(AdvertiseByCategoryWhereIn::class);
        }

        if ($request->query('filters', false)) {
            $this->advertiseRepository->pushCriteria(AdvertiseFilters::class);
        }

        $query = $this->advertiseRepository;

        if ($request->query('randomResult')) $query = $query->inRandomOrder();

        $result = $query->paginate($request->query('per_page'));

        return AdvertiseResource::collection($result);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function getSearchTexts(Request $request): LengthAwarePaginator {
        return $this->advertiseRepository->searchTexts($request->query('search'));
    }

    /**
     * @param Request $request
     * @param CategoryRepository $categoryRepository
     * @return AnonymousResourceCollection
     */
    public function getCategoriesAndAdvertisesCountBySearch(
        Request $request,
        CategoryRepository $categoryRepository
    ): AnonymousResourceCollection {
        $search = $request->query('search');
        $city = +$request->query('city_id');

        [$categories, $advertises] = $categoryRepository->searchCategoriesByAdvertiseCounts($search, $city);
        return CategoryResource::collection($categories)->additional(['advertises' => $advertises]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param AdvertiseRequest $request
     * @return AdvertiseResource|JsonResponse
     */
    public function store(AdvertiseRequest $request): AdvertiseResource|JsonResponse {
        try {
            $this->authorize(__FUNCTION__, Advertise::class);

            $advertise = $this->advertiseRepository->create($request->all());

            return new AdvertiseResource(
                $advertise->load(['category', 'gallery', 'city', 'answers'])
            );
        } catch (\Exception $e) {
            Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param $idOrSlug
     * @return AdvertiseResource|JsonResponse
     * @throws \Throwable
     */
    public function show(Request $request, $idOrSlug): AdvertiseResource|JsonResponse {
        try {
            $advertise = $this->advertiseRepository
                ->findWhere([
                    [is_numeric($idOrSlug) ? 'id' : 'slug', '=', $idOrSlug],
                    ['author', 'HAS', function(){}],
                ])
                ->first();

            throw_if(!(bool)$advertise, new ModelNotFoundException(__('messages.ITEM_NOTFOUND')));

            return AdvertiseResource::make($advertise);
        } catch (\Exception | ModelNotFoundException $e) {
            Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param AdvertiseRequest $request
     * @param Advertise $advertise
     * @return AdvertiseResource|JsonResponse
     */
    public function update(AdvertiseRequest $request, Advertise $advertise): AdvertiseResource|JsonResponse {
        try {
            $this->authorize(__FUNCTION__, $advertise);

            $advertise = $this->advertiseRepository->updateAdvertise($request->all(), $advertise);

            return new AdvertiseResource($advertise->load(['category', 'gallery', 'city', 'answers']));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }

    /**
     * @param AdvertisesRequest $request
     * @return JsonResponse
     */
    public function addFavorite(AdvertisesRequest $request): JsonResponse {

        $advertiseIds = [];

        foreach ($request->get('advertises') as $id) {
            $advertiseIds[$id] = [
                'type' => AdvertiseStatistic::Favorite,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        $request->user()->favorites()->attach($advertiseIds);
        return $this->success('', __('messages.SUCCESS_OPERATED'));
    }

    /**
     * @param AdvertisesRequest $request
     * @return JsonResponse
     */
    public function detachFavorite(AdvertisesRequest $request): JsonResponse {
        $request->user()->favorites()->detach($request->get('advertises'));
        return $this->success('', __('messages.SUCCESS_OPERATED'));
    }

    /**
     * @param Advertise $advertise
     * @param Media $media
     * @throws AuthorizationException
     * @return JsonResponse
     */
    public function deletePicture(Advertise $advertise, Media $media): JsonResponse {
        $this->authorize(__FUNCTION__, [$advertise, $media]);

        $media->delete();

        return $this->success('', __('messages.ITEM_DELETED'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param AdvertisesRequest $request
     * @param $action
     * @throws AuthorizationException
     * @return JsonResponse
     */
    public function changeProductStatusOrDeleteProduct(AdvertisesRequest $request, $action): JsonResponse {
        $advertises = $this->advertiseRepository->findWhereIn('id', $request->get('advertises'));

        $active = AdvertiseStatus::fromValue(AdvertiseStatus::Active);
        $inactive = AdvertiseStatus::fromValue(AdvertiseStatus::InActive);

        foreach ($advertises as $advertise) {
            $this->authorize(__FUNCTION__, $advertise);

            if ($action === 'delete') {
                $advertise->delete();
            }

            if ($action === 'draft') {
                $advertise->update(['status' => AdvertiseStatus::Draft]);
            }

            if ($action === 'moderation') {
                $advertise->update(['status' => AdvertiseStatus::NotVerified]);
            }

            if ($action === 'deactivate' && $active->is($advertise->status)) {
                $advertise->update(['status' => AdvertiseStatus::InActive]);
            }

            if ($action === 'activate' && $inactive->is($advertise->status)) {
                $advertise->update(['status' => AdvertiseStatus::Active]);
            }
        }

        return $this->success('', __('messages.SUCCESS_OPERATED'));
    }

    /**
     * @param Advertise $advertise
     * @return JsonResponse
     */
    public function addStatisticsPhoneView(Advertise $advertise): JsonResponse {
        $advertise->increment('show_phone');
        $advertise->save();
        return $this->success('', __('messages.SUCCESS_OPERATED'));
    }

    /**
     * @param Advertise $advertise
     * @return JsonResponse
     */
    public function addStatisticsDetailsView(Advertise $advertise): JsonResponse {
        $advertise->increment('show_details');
        $advertise->save();
        return $this->success('', __('messages.SUCCESS_OPERATED'));
    }

    /**
     * @param Advertise $advertise
     * @return JsonResponse
     */
    public function addStatisticsFavorite(Advertise $advertise): JsonResponse {
        $advertise->increment('added_favorites');
        $advertise->save();
        return $this->success('', __('messages.SUCCESS_OPERATED'));
    }
}
