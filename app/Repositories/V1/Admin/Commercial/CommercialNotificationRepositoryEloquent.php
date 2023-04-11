<?php

namespace App\Repositories\V1\Admin\Commercial;

use App\Criteria\V1\SearchCriteria;
use App\Repositories\V1\Base\BaseRepository;
use App\Traits\UploadAble;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class CommercialNotificationRepositoryEloquent
 * @package App\Repositories\V1\Admin\Commercial
 */
class CommercialNotificationRepositoryEloquent extends BaseRepository implements CommercialNotificationRepository
{
    use UploadAble;

    /**
     * @var array
     */
    protected $fieldSearchable = [
        'type',
        'status',
        'title' => 'LIKE',
        'description' => 'LIKE',
        'created_at' => 'between',
    ];

    /**
     * @var string
     */
    public string $collection_name = \App\Enums\MediaCollections::COMMERCIAL_NOTIFICATION_BANNER;

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
        return \App\Models\CommercialNotification::class;
    }
}
