<?php

namespace App\Services\V1\Admin;


use App\Models\Category;
use App\Repositories\V1\Admin\Category\CategoryRepository;

/**
 * Class CategoryService
 * @package App\Services\V1\Admin
 */
class CategoryService {

    /**
     * SupportService constructor.
     * @param CategoryRepository $categoryRepository
     */
    public function __construct(
        private CategoryRepository $categoryRepository,
    ) { }

    /**
     * @return CategoryRepository
     */
    public function category(): CategoryRepository {
        return $this->categoryRepository;
    }

    /**
     * @param int $category_id
     * @return array
     */
    public function getParentRecursionIds(int $category_id): array {
        $categoriesIds = [];
        $category = Category::with('parentCategories')->find($category_id);

        $this->recursiveParentsTakeCategoryId($category->toArray(), $categoriesIds);

        return $categoriesIds;
    }

    /**
     * @param int $category_id
     * @return array
     */
    public function getChildRecursionIds(int $category_id): array {
        $categoriesIds = [];

        $category = Category::with('allSubCategoriesBySelect')->find($category_id);

        array_push($categoriesIds, $category->id);

        $this->recursiveBottomTakeCategoryId($category->allSubCategoriesBySelect->toArray(), $categoriesIds);

        return $categoriesIds;
    }

    /**
     * @param array $category
     * @param array $ids
     * @return void
     */
    private function recursiveParentsTakeCategoryId(array $category, array &$ids): void {
        $ids[] = $category['id'];

        if (isset($category['parent_categories']) && !empty($category['parent_categories'])) {
            $this->recursiveParentsTakeCategoryId($category['parent_categories'][0], $ids);
        }
    }

    /**
     * @param array $categories
     * @param array $ids
     * @return void
     */
    private function recursiveBottomTakeCategoryId(array $categories, array &$ids): void {
        foreach ($categories as $category) {
            $ids[] = $category['id'];

            if (isset($category['all_sub_categories']) && !empty($category['all_sub_categories'])) {
                $this->recursiveBottomTakeCategoryId($category['all_sub_categories'], $ids);
            }
        }
    }
}
