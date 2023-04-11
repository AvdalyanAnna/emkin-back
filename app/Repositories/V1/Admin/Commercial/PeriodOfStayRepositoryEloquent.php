<?php

namespace App\Repositories\V1\Admin\Commercial;

use App\Repositories\V1\Base\BaseRepository;

/**
 * Class PeriodOfStayRepositoryEloquent
 * @package App\Repositories\V1\Admin\Commercial
 */
class PeriodOfStayRepositoryEloquent extends BaseRepository implements PeriodOfStayRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'status',
        'title',
        'email',
        'description',
        'period',
    ];

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot() {}

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model(): string {
        return \App\Models\PeriodOfStay::class;
    }
}
