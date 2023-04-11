<?php

namespace App\Repositories\V1\Admin;

use App\Repositories\V1\Base\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class RefusalRepositoryEloquent
 * @package App\Repositories\V1\Admin\Commercial
 */
class RefusalRepositoryEloquent extends BaseRepository implements RefusalRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'refusal',
        'type'
    ];

    /**
     * Boot up the repository, pushing criteria
     * @throws RepositoryException
     */
    public function boot() {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model(): string {
        return \App\Models\Refusal::class;
    }
}
