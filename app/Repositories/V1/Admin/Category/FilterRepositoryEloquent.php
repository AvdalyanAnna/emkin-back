<?php

namespace App\Repositories\V1\Admin\Category;

use App\Models\Category;
use App\Models\Filter;
use App\Models\FilterAnswer;
use Illuminate\Container\Container as Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class FilterRepositoryEloquent
 * @package App\Repositories\V1\Admin\Category
 */
class FilterRepositoryEloquent extends BaseRepository implements FilterRepository {

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
            'category_id',
            $localeName
        ];
    }

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model(): string {
        return \App\Models\Filter::class;
    }

    /**
     * Boot up the repository, pushing criteria
     * @throws RepositoryException
     */
    public function boot() {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    /**
     * @param array $attributes
     * @throws ValidatorException|\Throwable
     * @return mixed
     */
    public function create(array $attributes): mixed {

        DB::transaction(function () use ($attributes, &$filters) {
            foreach ($attributes['filters'] as $attribute) {
                $filter = parent::create($attribute);
                $answers = collect($attribute['answers'])->map(fn($item) => new FilterAnswer($item));
                $filter->answers()->saveMany($answers);
                $filters[] = $filter->load($filter->getRelations());
            }
        });

        return $filters;
    }

    /**
     * @param array $attributes
     * @throws \Throwable
     * @return array
     */
    public function updateFilter(array $attributes): array {

        DB::transaction(function () use ($attributes, &$filters) {
            foreach ($attributes['filters'] as $attribute) {
                $filter = parent::updateOrCreate(['id' => $attribute['id']], $attribute)->load('answers');
                $filter->answers()->createUpdateOrDeleteAnswers($attribute['answers'], $filter);
                $filters[] = $filter->load('answers');
            }
        });

        return $filters;
    }
}
