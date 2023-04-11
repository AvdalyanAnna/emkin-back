<?php

namespace App\Repositories\V1\Admin\Category;

use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Interface FilterRepository
 * @package App\Repositories\V1\Admin\Category
 * @method pushCriteria(string $class)
 */
interface FilterRepository extends RepositoryInterface
{
    /**
     * @param array $attributes
     * @return array
     */
    public function updateFilter(array $attributes): array;
}
