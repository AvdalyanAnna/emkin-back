<?php

namespace App\Repositories\V1\Admin\Commercial;

use App\Criteria\V1\SearchCriteria;
use App\Repositories\V1\Base\BaseRepository;
use App\Traits\UploadAble;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class UserRepositoryEloquent.
 *
 * @package namespace App\Repositories\Users;
 */
class CommercialUserRepositoryEloquent extends BaseRepository implements CommercialUserRepository
{
    use UploadAble;

    /**
     * @var array
     */
    protected $fieldSearchable = [
        'status',
        'period_of_stay_id',
        'email',
    ];

    /**
     * @var string
     */
    public string $collection_name = \App\Enums\MediaCollections::COMMERCIAL_USER_AVATAR;

    /**
     * Boot up the repository, pushing criteria
     * @throws RepositoryException
     */
    public function boot() {
        config(['audit.events' => ['created', 'updated']]);
        $this->pushCriteria(app(SearchCriteria::class));
    }

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model(): string {
        return \App\Models\CommercialUsers::class;
    }
}
