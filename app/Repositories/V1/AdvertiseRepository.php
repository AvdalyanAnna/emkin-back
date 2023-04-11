<?php

namespace App\Repositories\V1;

use App\Http\Requests\V1\AdvertisesRequest;
use App\Models\Advertise;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Interface AdvertiseRepository
 * @package App\Repositories\V1\Admin\Category
 * @method getByCriteria(\App\Criteria\V1\Users\BannedUserCriteria $param)
 * @method pushCriteria(string $class)
 * @method inRandomOrder()
 */
interface AdvertiseRepository extends RepositoryInterface
{
    /**
     * @param array $attributes
     * @param Advertise $advertise
     * @return mixed
     */
    public function updateAdvertise(array $attributes, Advertise $advertise): Advertise;

    /**
     * @param AdvertisesRequest $request
     * @param Advertise $advertise
     * @param int|null $moderator_id
     * @return void
     */
    public function changeStatus(AdvertisesRequest $request, Advertise $advertise, int $moderator_id = null): void;
}
