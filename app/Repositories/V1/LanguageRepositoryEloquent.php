<?php

namespace App\Repositories\V1;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Traits\CacheableRepository;

/**
 * Class LanguageRepositoryRepositoryEloquent.
 *
 * @package namespace App\Repositories\V1;
 */
class LanguageRepositoryEloquent extends BaseRepository implements LanguageRepository
{
    use CacheableRepository;

    /**
     * @var int
     */
    protected int $cacheMinutes = 30000;

    /**
     * @var array|string[]
     */
    protected array $cacheOnly = ['all'];

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return \App\Models\Language::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot() {}
}
