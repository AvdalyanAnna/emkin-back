<?php

namespace App\Criteria\V1\Advertises;

use Illuminate\Http\Request;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class SearchCriteria
 * @package App\Criteria\V1
 */
class AdvertiseFilters implements CriteriaInterface
{
    protected Request $request;

    /**
     * SearchCriteria constructor.
     */
    public function __construct()
    {
        $this->request = app(Request::class);
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
        $filters = json_decode($this->request->query('filters'), json_encode([]));

        foreach ($filters as $answers) $model = $model->where(function ($q) use ($answers) {
            foreach ($answers as $answer) $q->orWhere('filters', 'LIKE', "%[$answer]%");
        });

        return $model;
    }
}
