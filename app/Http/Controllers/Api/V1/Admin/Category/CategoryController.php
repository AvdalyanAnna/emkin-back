<?php

namespace App\Http\Controllers\Api\V1\Admin\Category;

use App\Criteria\V1\SearchCriteria;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Admin\Category\CategoryOrderUpdateRequest;
use App\Http\Requests\V1\Admin\Category\CategoryRequest;
use App\Http\Resources\V1\Admin\Category\CategoryResource;
use App\Models\Category;
use App\Repositories\V1\Admin\Category\CategoryRepository;
use App\Traits\ApiResponseAble;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\Pure;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 * Class CategoryController
 * @package App\Http\Controllers\Api\V1\Admin
 */
class CategoryController extends Controller {

    use ApiResponseAble;

    /**
     * CategoryController constructor.
     * @param CategoryRepository $categoryRepository
     */
    public function __construct(private CategoryRepository $categoryRepository) { }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function list(Request $request): AnonymousResourceCollection {

        $query = $this->categoryRepository;

        if($search = $request->get(config('repository.criteria.params.search', 'search'))) {
            $query = $this->addQueryForParentCategory($search, $query);
        }

        return CategoryResource::collection(
            $query->paginate($request->query('per_page'))
        );
    }

    /**
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function topCategories(Request $request): AnonymousResourceCollection {
        $categories = $this->categoryRepository->getTopCategories($request->query('name'));

        return CategoryResource::collection($categories);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @throws RepositoryException
     * @return AnonymousResourceCollection
     */
    public function parentCategories(Request $request): AnonymousResourceCollection {
        $perPage = $request->query('per_page');
        $condition = ['parent_id' => null];

        return CategoryResource::collection(
            $this->categoryRepository
                ->with(['picture','allSubCategories'])
                ->findWherePaginate($condition, ['*'], $perPage)
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CategoryRequest $request
     * @return CategoryResource|JsonResponse
     */
    public function store(CategoryRequest $request): CategoryResource|JsonResponse {
        try {
            $category = $this->categoryRepository->create($request->all());

            return new CategoryResource(
                $category->load($category->getRelations())
            );
        } catch (\Exception $e) {
            Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param CategoryRequest $request
     * @param Category $category
     * @return CategoryResource|JsonResponse
     */
    public function update(CategoryRequest $request, Category $category): CategoryResource|JsonResponse {
        try {
            $category = $this->categoryRepository->updateCategory($request->all(), $category);

            return new CategoryResource($category->load($category->getRelations()));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param Category $category
     * @return CategoryResource
     */
    public function show(Category $category): CategoryResource {
        return new CategoryResource(
            $category->load($category->getRelations())
        );
    }

    /**
     * Display the specified resource.
     *
     * @param CategoryOrderUpdateRequest $request
     * @return JsonResponse
     */
    public function orderUpdate(CategoryOrderUpdateRequest $request): JsonResponse {
        \Batch::update(new Category(), $request->get('orders'), 'id');
        return $this->success(__('messages.SUCCESS_OPERATED'));
    }

    /**
     * Display the specified resource.
     *
     * @param Category $category
     * @throws \Throwable
     * @return CategoryResource
     */
    public function duplicateCategory(Category $category): CategoryResource {
        $category = $this->categoryRepository->duplicateCategory($category);

        return new CategoryResource($category->load([
            'filters.answers',
            'picture',
        ]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param CategoryRequest $request
     * @return JsonResponse
     */
    public function destroy(CategoryRequest $request): JsonResponse {
        try {
            $this->categoryRepository->multipleDelete($request->get('categories', []));

            return $this->success('', __('messages.ITEM_DELETED'));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }

    /**
     * @param $search
     * @param $query
     * @return mixed
     */
    protected function addQueryForParentCategory($search, $query): mixed {

        $searchData = app(SearchCriteria::class)->parserSearchData($search);

        if (isset($searchData['parent_category'])) {
            return $query->whereNull('parent_id');
        }

        return $query;
    }
}
