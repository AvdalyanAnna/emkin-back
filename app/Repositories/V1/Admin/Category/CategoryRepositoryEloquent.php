<?php

namespace App\Repositories\V1\Admin\Category;

use App\Criteria\V1\SearchCriteria;
use App\Models\Category;
use App\Repositories\V1\Base\BaseRepository;
use App\Traits\UploadAble;
use Illuminate\Container\Container as Application;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\ArrayShape;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class CategoryRepositoryEloquent.
 *
 * @package namespace App\Repositories\Api\V1\Admin;
 */
class CategoryRepositoryEloquent extends BaseRepository implements CategoryRepository {

    use UploadAble;

    /**
     * @var array
     */
    protected $fieldSearchable = [];

    /**
     * CategoryRepositoryEloquent constructor.
     * @param Application $app
     */
    public function __construct(Application $app) {
        parent::__construct($app);

        $localeName = "name->" . app()->getLocale();

        $this->fieldSearchable = [
            'parent_id',
            $localeName
        ];
    }

    /**
     * @var string
     */
    public string $collection_name = \App\Enums\MediaCollections::PICTURE_COLLECTION;

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model(): string {
        return \App\Models\Category::class;
    }

    /**
     * Boot up the repository, pushing criteria
     * @throws RepositoryException
     */
    public function boot() {
        $this->pushCriteria(app(SearchCriteria::class));
    }

    /**
     * @param array $attributes
     * @throws ValidatorException|\Throwable
     * @return mixed
     */
    public function create(array $attributes): mixed {
        DB::transaction(function () use ($attributes, &$category) {
            $category = parent::create($attributes);
            if (isset($attributes['media_id'])) {
                $this->setExistsMediaFileToModel($category, [
                    'media_id' => $attributes['media_id']
                ]);
            }
        });

        return $category->fresh();
    }

    /**
     * @param array $attributes
     * @param Category $category
     * @throws ValidatorException|\Throwable
     * @return mixed
     */
    public function updateCategory(array $attributes, Category $category): Category {

        DB::transaction(function () use ($attributes, &$category) {
            if (isset($attributes['media_id'])) {
                $category->clearMediaCollection($this->collection_name);

                $this->setExistsMediaFileToModel($category, [
                    'media_id' => $attributes['media_id']
                ]);
            }

            $category = parent::update($attributes, $category->id);
        });

        return $category;
    }

    /**
     * @param Category $category
     * @throws \Throwable
     * @return Category
     */
    public function duplicateCategory(Category $category): Category {
        \DB::transaction(function () use (&$category) {
            $mediaId = null;

            if ($category->hasMedia($this->collection_name)) {
                $mediaId = $category->picture->id;
            }

            $category = $category->duplicate();

            if($mediaId) {
                $this->setExistsMediaFileToModel($category, [
                    'media_id' => $mediaId
                ]);
            }
        });

        return $category->fresh();
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getTopCategories(string $name): mixed {
        $likeAdvertiseByName = function ($query) use ($name) {
            $query->where('name', 'LIKE', "%$name%");
        };

        return $this->getModel()::whereHas('advertises', $likeAdvertiseByName)
            ->withCount(['advertises as advertise_count' => $likeAdvertiseByName])
            ->orderBy('advertise_count', 'desc')
            ->with('picture')
            ->take(4)
            ->get();
    }

    /**
     * @param string $search
     * @param int|null $city_id
     * @return array
     */
    public function searchCategoriesByAdvertiseCounts(string $search, ?int $city_id = null): array {

        $categories = [];
        $advertises = [];

        \DB::table('advertises as adv')
            ->when($city_id, function ($q) use ($city_id) {
                $q->where('city_id', '=', $city_id);
            })
            ->select(['adv.name as product_name', DB::raw('count(*) as total'), 'adv.category_id', 'cat.name as category_name'])
            ->join('categories as cat', 'cat.id', '=', 'adv.category_id')
            ->where('adv.name', 'LIKE', "$search%")
            ->groupBy('product_name', 'adv.category_id')
            ->orderByDesc('total')
            ->chunk(50, function ($items) use (&$categories, &$advertises, &$oldName) {
                foreach ($items as $key => $item) {
                    if ($key <= 2) {
                        $category = Category::with('parentCategories:id,slug,name,parent_id')->find($item->category_id);
                        $category->product_name = $item->product_name;
                        array_push($categories, $category);
                    }

                    $oldName = $item->product_name;

                    if (key_exists($oldName, $advertises)) {
                        $advertises[$oldName]['total'] = $advertises[$oldName]['total']+$item->total;
                    } else {
                        $advertises[$oldName] = [
                            'total' => $item->total,
                            'product_name' => $item->product_name,
                        ];
                    }

                    if (count($advertises) == 5) {
                        return false;
                    }
                }
            });

        return [$categories, $advertises];
    }

    /**
     * @param array $categories_ids
     * @return void
     */
    public function multipleDelete(array $categories_ids): void {
        $this->deleteWhere([['id', 'IN', $categories_ids]]);
    }
}
