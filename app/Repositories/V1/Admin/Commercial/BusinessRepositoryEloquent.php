<?php

namespace App\Repositories\V1\Admin\Commercial;

use App\Criteria\V1\SearchCriteria;
use App\Repositories\V1\Base\BaseRepository;
use App\Traits\UploadAble;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class BusinessRepositoryEloquent
 * @package App\Repositories\V1\Admin\Commercial
 */
class BusinessRepositoryEloquent extends BaseRepository implements BusinessRepository
{
    use UploadAble;

    /**
     * @var array
     */
    protected $fieldSearchable = [
        'status',
        'client_id',
        'name',
        'link',
        'location',
        'type',
    ];

    /**
     * @var string
     */
    public string $collection_name = \App\Enums\MediaCollections::COMMERCIAL_BUSINESS_BANNER;

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
        return \App\Models\CommercialBusiness::class;
    }
}
