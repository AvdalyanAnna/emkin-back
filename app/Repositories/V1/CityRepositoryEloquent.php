<?php

namespace App\Repositories\V1;

use App\Models\City;
use App\Repositories\V1\Base\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;

/**
 * Class CityRepositoryEloquent
 * @package App\Repositories\V1
 */
class CityRepositoryEloquent extends BaseRepository implements CityRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'id',
        'name' => 'LIKE',
        'order' => 'BETWEEN',
        'state_code',
        'state_id'
    ];

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model(): string {
        return \App\Models\City::class;
    }

    /**
     * Boot up the repository, pushing criteria
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function boot() {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    /**
     * @param array $data
     * @return object|null
     */
    public function findCityByLatLongAndCityName(array $data): object|null {
        return City::query()
            ->with('state_minimal_select')
            ->when(isset($data['city']), function ($q) use ($data) {
                $city = $data['city'];
                $q->where('name', '=', $city);
                $q->when(isset($data['state']), function ($q) use ($data) {
                    $q->whereHas('state', function ($q) use ($data) {
                        $state = $data['state'];
                        $q->where('name', '=', $state);
                    });
                });
            })->first();
    }
}
