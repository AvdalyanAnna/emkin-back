<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Criteria\V1\Users\BannedUserCriteria;
use App\Enums\Admin\Moderator\ModeratorStatisticsEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\AdvertisesRequest;
use App\Http\Resources\V1\AdvertiseResource;
use App\Http\Resources\V1\User\UserResource;
use App\Models\Advertise;
use App\Models\ModeratorStatistic;
use App\Models\User;
use App\Repositories\V1\AdvertiseRepository;
use App\Traits\ApiResponseAble;
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
        $this->advertiseRepository->setFieldSearchable([
            'id',
            'price' => 'between',
            'created_at' => 'between',
            'category_id' => 'in',
            'contacts',
            'contact_phone_numeric' => 'LIKE',
            'status',
            'author.first_name' => 'LIKE',
            'author.last_name' => 'LIKE',
            'name' => 'LIKE'
        ]);

        $this->advertiseRepository->pushCriteria(BannedUserCriteria::class);

        return AdvertiseResource::collection(
            $this->advertiseRepository->paginate($request->query('per_page'))
        );
    }

    /**
     * @param User $user
     * @return UserResource
     */
    public function otherAdvertises(User $user): UserResource {
        return new UserResource($user->load([
            'advertises.gallery',
            'advertises.category',
            'advertises.city'
        ]));
    }

    /**
     * @param Request $request
     * @param Advertise $advertise
     * @return AdvertiseResource
     */
    public function changeCategory(Request $request, Advertise $advertise): AdvertiseResource {

        $request->validate(['category_id' => 'required|exists:categories,id']);

        $advertise = $this->advertiseRepository->update($request->only('category_id'), $advertise->id);

        return new AdvertiseResource($advertise->load(['category', 'city']));
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param Advertise $advertise
     * @return AdvertiseResource|JsonResponse
     * @throws \Throwable
     */
    public function show(Request $request, Advertise $advertise): AdvertiseResource|JsonResponse {
        try {
            if (
                $request->user()->hasRole(config('roles.roles.moderator.name')) &&
                !ModeratorStatistic::existsStatistics(\user(), $advertise, ModeratorStatisticsEnum::VIEWED_ADS)
            ) {
                /**
                 * Сохраняем Статистику (Кол-во просмотренных объявлений (view details count))
                 */
                ModeratorStatistic::make([
                    'moderator_id' => $request->user()->id,
                    'advertise_id' => $advertise->id,
                    'type' => ModeratorStatisticsEnum::VIEWED_ADS,
                ]);
            }

            return AdvertiseResource::make($advertise->load([
                'city', 'author.avatar', 'category', 'gallery', 'answers.filter'
            ]));
        } catch (\Exception | ModelNotFoundException $e) {
            Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param AdvertisesRequest $request
     * @throws \Throwable
     * @return AdvertiseResource|JsonResponse
     */
    public function changeStatus(AdvertisesRequest $request): AdvertiseResource|JsonResponse {
        try {
            $advertises = $this->advertiseRepository->findWhereIn('id', $request->get('advertises'));

            \DB::transaction(function () use ($advertises, $request) {
                foreach ($advertises as $advertise) {
                    $this->authorize('changeStatus', $advertise);
                    $this->advertiseRepository->changeStatus($request, $advertise, user()->id);
                }
            });

            return $this->success('ok');
        } catch (\Exception $e) {
            Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }
}
