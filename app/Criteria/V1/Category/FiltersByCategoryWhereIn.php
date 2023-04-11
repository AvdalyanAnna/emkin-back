<?php

namespace App\Criteria\V1\Category;

use App\Services\V1\Admin\CategoryService;
use Illuminate\Http\Request;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class FiltersByCategoryWhereIn
 * @package App\Criteria\V1\Advertises
 */
class FiltersByCategoryWhereIn implements CriteriaInterface
{
    protected Request $request;
    protected CategoryService $categoryService;

    /**
     * SearchCriteria constructor.
     */
    public function __construct()
    {
        $this->request = app(Request::class);
        $this->categoryService = app(CategoryService::class);
    }

    /**
     * Apply criteria in query repository
     *
     * @param string $model
     * @param RepositoryInterface $repository
     *
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository)
    {
        $categoriesIds = $this->categoryService->getParentRecursionIds(
            $this->request->query('category_id')
        );

        return $model->whereIn('category_id', $categoriesIds);
    }
}
