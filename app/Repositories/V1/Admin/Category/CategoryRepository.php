<?php

namespace App\Repositories\V1\Admin\Category;

use App\Models\Category;
use App\Repositories\V1\Base\BaseContract;

/**
 * Interface CategoryRepository.
 *
 * @package namespace App\Repositories\Api\V1\Admin;
 */
interface CategoryRepository extends BaseContract
{
    /**
     * @param array $attributes
     * @param Category $category
     * @return mixed
     */
    public function updateCategory(array $attributes, Category $category): Category;

    /**
     * @param array $categories_ids
     */
    public function multipleDelete(array $categories_ids): void;
}
