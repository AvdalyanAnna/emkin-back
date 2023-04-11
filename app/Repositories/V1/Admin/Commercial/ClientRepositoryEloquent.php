<?php

namespace App\Repositories\V1\Admin\Commercial;

use App\Repositories\V1\Base\BaseRepository;
use App\Traits\UploadAble;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class ClientRepositoryEloquent
 * @package App\Repositories\V1\Admin\Commercial
 */
class ClientRepositoryEloquent extends BaseRepository implements ClientRepository
{
    use UploadAble;

    /**
     * @var array
     */
    protected $fieldSearchable = [
        'first_name' => 'LIKE',
        'last_name' => 'LIKE',
        'company' => 'LIKE',
        'email',
        'phone',
    ];

    /**
     * @var string
     */
    public string $collection_name = \App\Enums\MediaCollections::COMMERCIAL_CLIENT_AVATAR;

    /**
     * Boot up the repository, pushing criteria
     * @throws RepositoryException
     */
    public function boot() {
        config(['audit.events' => ['created', 'updated']]);
        $this->pushCriteria(app(RequestCriteria::class));
    }

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model(): string {
        return \App\Models\Client::class;
    }
}
