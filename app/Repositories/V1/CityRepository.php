<?php

namespace App\Repositories\V1;

use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Interface CityRepository
 * @package App\Repositories\V1
 */
interface CityRepository extends RepositoryInterface
{

    /**
     * @param array $data
     * @return object|null
     */
    public function findCityByLatLongAndCityName(array $data): object|null;
}
