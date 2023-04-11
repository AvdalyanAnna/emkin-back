<?php

namespace App\Repositories\V1;

use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Class StateRepositoryEloquent
 * @package App\Repositories\V1
 */
class StateRepositoryEloquent extends BaseRepository implements StateRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model(): string {
        return \App\Models\State::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot() {}
}
