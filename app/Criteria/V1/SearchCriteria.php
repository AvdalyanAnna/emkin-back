<?php

namespace App\Criteria\V1;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class SearchCriteria
 * @package App\Criteria\V1
 */
class SearchCriteria implements CriteriaInterface
{
    /**
     * @var Request
     */
    protected Request $request;

    /**
     * SearchCriteria constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Apply criteria in query repository
     *
     * @param string $model
     * @param RepositoryInterface $repository
     *
     * @throws \Exception
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository): mixed {
        $fieldsSearchable = $repository->getFieldsSearchable();
        $search = $this->request->get(config('repository.criteria.params.search', 'search'), null);
        $searchFields = $this->request->get(config('repository.criteria.params.searchFields', 'searchFields'), null);
        $filter = $this->request->get(config('repository.criteria.params.filter', 'filter'), null);
        $orderBy = $this->request->get(config('repository.criteria.params.orderBy', 'orderBy'), null);
        $sortedBy = $this->request->get(config('repository.criteria.params.sortedBy', 'sortedBy'), 'asc');
        $with = $this->request->get(config('repository.criteria.params.with', 'with'), null);
        $withCount = $this->request->get(config('repository.criteria.params.withCount', 'withCount'), null);
        $searchJoin = $this->request->get(config('repository.criteria.params.searchJoin', 'searchJoin'), null);
        $sortedBy = !empty($sortedBy) ? $sortedBy : 'asc';

        if ($search && is_array($fieldsSearchable) && count($fieldsSearchable)) {
            $searchFields = is_array($searchFields)
            || is_null($searchFields)
                ? $searchFields
                : explode(';', $searchFields);

            $isFirstField = true;
            $searchData = $this->parserSearchData($search);
            $fields = $this->parserFieldsSearch($fieldsSearchable, $searchFields, array_keys($searchData));
            $search = $this->parserSearchValue($search);
            $modelForceAndWhere = strtolower($searchJoin) === 'and';

            /** @var Model $model */
            $model = $model->when(isset($searchData['search_text']), function ($query) use ($searchData) {
                $text = $searchData['search_text'];
                $query->where(function ($q) use ($text) {
                    $text = mb_convert_encoding($text, 'UTF-8', 'auto');
                    $q->where('name', 'LIKE', "%$text%");
                });
            });

            $model = $model->when((isset($searchData['city_id']) && (isset($searchData['mil']) || isset($searchData['km']))),
                function ($query) use ($searchData) {
                    $city = DB::table('cities')->select(['latitude','longitude'])->find($searchData['city_id']);
                    if($city) {
                        $milOrKm = isset($searchData['mil']) ? 3959 : 6371;
                        $query
                            ->selectRaw("advertises.*, (
                                $milOrKm * acos (
                                  cos (radians($city->latitude))
                                  * cos(radians(latitude))
                                  * cos(radians(longitude) - radians($city->longitude))
                                  + sin(radians($city->latitude))
                                  * sin(radians(latitude))
                                )
                              ) AS distance"
                            )
                            ->having('distance', '<', $searchData['mil'] ?? $searchData['km'])
                            ->orderBy('distance');
                    }
                }
            );

            foreach ($fields as $field => $condition) {

                if (is_numeric($field)) {
                    $field = $condition;
                    $condition = "=";
                }

                $value = null;

                $condition = trim(strtolower($condition));

                if (isset($searchData[$field])) {
                    $value = ($condition == "like" || $condition == "ilike") ? "%{$searchData[$field]}%" : $searchData[$field];
                } else {
                    if (!is_null($search) && !in_array($condition, ['in', 'between'])) {
                        $value = ($condition == "like" || $condition == "ilike") ? "%{$search}%" : $search;
                    }
                }

                $relation = null;
                if (stripos($field, '.')) {
                    $explode = explode('.', $field);
                    $field = array_pop($explode);
                    $relation = implode('.', $explode);
                }
                if ($condition === 'in' || $condition === 'not in') {
                    $value = explode(',', $value);
                    if (trim($value[0]) === "" || $field == $value[0]) {
                        $value = null;
                    }
                }
                if ($condition === 'between') {
                    $value = explode(',', $value);
                    if (count($value) < 2) {
                        $value = null;
                    }
                }
                $modelTableName = $model->getModel()->getTable();
                if ($isFirstField || $modelForceAndWhere) {
                    if (!is_null($value)) {
                        if (!is_null($relation)) {
                            $model = $model->whereHas($relation, function ($query) use ($field, $condition, $value) {
                                if ($condition === 'in') {
                                    $query->whereIn($field, $value);
                                } elseif ($condition === 'between') {
                                    $query->whereBetween($field, $value);
                                } else {
                                    $query->where($field, $condition, $value);
                                }
                            });
                        } else {
                            if ($condition === 'in') {
                                $model = $model->whereIn($modelTableName . '.' . $field, $value);
                            } elseif ($condition === 'not in') {
                                $model = $model->whereNotIn($modelTableName . '.' . $field, $value);
                            } elseif ($condition === 'between') {
                                $isDateValid = \DateTime::createFromFormat('Y-m-d', $value[1]) !== false;
                                if ($isDateValid && $value[0] == $value[1]) {
                                    $model = $model->whereDate($modelTableName . '.' . $field, $value[0]);
                                } elseif($isDateValid && $value[0] != $value[1]) {
                                    $model = $model
                                            ->whereDate($modelTableName . '.' . $field, ">=", $value[0])
                                            ->whereDate($modelTableName . '.' . $field, "<=", $value[1]);
                                } else {
                                    $model = $model->whereBetween($modelTableName . '.' . $field, $value);
                                }
                            } else {
                                if ($condition === 'like' && str_contains($this->request->url(), 'categories')) {
                                    $value = str_replace('%', '', $value);
                                    $value = mb_convert_encoding($value, 'UTF-8', 'auto');
                                    $model = $model->where(function($q) use ($modelTableName, $field, $condition, $value) {
                                       $q->where($modelTableName . '.' . $field, $condition, "$value%")
                                          ->orWhere($modelTableName . '.' . $field, $condition, mb_convert_case($value, MB_CASE_TITLE, "UTF-8") . '%')
                                          ->orWhere($modelTableName . '.' . $field, $condition, mb_strtolower($value) . '%');
                                    });
                                } else {

                                    if ($condition === 'like') {
                                        $model = $model->where($modelTableName . '.' . $field, $condition, mb_convert_encoding($value, 'UTF-8', 'auto'));
                                    } else {
                                        $model = $model->where($modelTableName . '.' . $field, $condition, $value);
                                    }
                                }
                            }
                        }
                        $isFirstField = false;
                    }
                } else {
                    if (!is_null($value)) {
                        if (!is_null($relation)) {
                            $model = $model->orWhereHas($relation, function ($query) use ($field, $condition, $value) {
                                if ($condition === 'in') {
                                    $query->whereIn($field, $value);
                                } elseif ($condition === 'between') {
                                    $query->whereBetween($field, $value);
                                } else {
                                    $query->where($field, $condition, $value);
                                }
                            });
                        } else {
                            if ($condition === 'in') {
                                $model = $model->orWhereIn($modelTableName . '.' . $field, $value);
                            } elseif ($condition === 'between') {
                                $model = $model->whereBetween($modelTableName . '.' . $field, $value);
                            } elseif ($condition === 'not in') {
                                $model = $model->orWhereNotIn($modelTableName . '.' . $field, $value);
                            } else {
                                $model = $model->orWhere($modelTableName . '.' . $field, $condition, $value);
                            }
                        }
                    }
                }
            }
        }

        if (isset($orderBy) && !empty($orderBy)) {
            $orderBySplit = explode(';', $orderBy);
            if(count($orderBySplit) > 1) {
                $sortedBySplit = explode(';', $sortedBy);
                foreach ($orderBySplit as $orderBySplitItemKey => $orderBySplitItem) {
                    $sortedBy = isset($sortedBySplit[$orderBySplitItemKey]) ? $sortedBySplit[$orderBySplitItemKey] : $sortedBySplit[0];
                    $model = $this->parserFieldsOrderBy($model, $orderBySplitItem, $sortedBy);
                }
            } else {
                $model = $this->parserFieldsOrderBy($model, $orderBySplit[0], $sortedBy);
            }
        }

        if (isset($filter) && !empty($filter)) {
            if (is_string($filter)) {
                $filter = explode(';', $filter);
            }

            $model = $model->select($filter);
        }

        if ($with) {
            $with = explode(';', $with);
            $model = $model->with($with);
        }

        if ($withCount) {
            $withCount = explode(';', $withCount);
            $model = $model->withCount($withCount);
        }

        return $model;
    }


    /**
     * @param $model
     * @param $orderBy
     * @param $sortedBy
     * @return mixed
     */
    protected function parserFieldsOrderBy($model, $orderBy, $sortedBy): mixed {
        $split = explode('|', $orderBy);
        if(count($split) > 1) {
            /*
             * ex.
             * products|description -> join products on current_table.product_id = products.id order by description
             *
             * products:custom_id|products.description -> join products on current_table.custom_id = products.id order
             * by products.description (in case both tables have same column name)
             */
            $table = $model->getModel()->getTable();
            $sortTable = $split[0];
            $sortColumn = $split[1];

            $split = explode(':', $sortTable);
            $localKey = '.id';
            if (count($split) > 1) {
                $sortTable = $split[0];

                $commaExp = explode(',', $split[1]);
                $keyName = $table.'.'.$split[1];
                if (count($commaExp) > 1) {
                    $keyName = $table.'.'.$commaExp[0];
                    $localKey = '.'.$commaExp[1];
                }
            } else {
                /*
                 * If you do not define which column to use as a joining column on current table, it will
                 * use a singular of a join table appended with _id
                 *
                 * ex.
                 * products -> product_id
                 */
                $prefix = Str::singular($sortTable);
                $keyName = $table.'.'.$prefix.'_id';
            }

            $model = $model
                ->leftJoin($sortTable, $keyName, '=', $sortTable.$localKey)
                ->orderBy($sortColumn, $sortedBy)
                ->addSelect($table.'.*');
        } else {
            $model = $model->orderBy($orderBy, $sortedBy);
        }
        return $model;
    }

    /**
     * @param $search
     *
     * @return array
     */
    public function parserSearchData($search): array {
        $searchData = [];

        if (stripos($search, ':')) {
            $fields = explode(';', $search);

            foreach ($fields as $row) {
                try {
                    list($field, $value) = explode(':', $row);
                    $searchData[$field] = $value;
                } catch (\Exception $e) {
                    //Surround offset error
                }
            }
        }

        return $searchData;
    }

    /**
     * @param $search
     *
     * @return null
     */
    protected function parserSearchValue($search)
    {

        if (stripos($search, ';') || stripos($search, ':')) {
            $values = explode(';', $search);
            foreach ($values as $value) {
                $s = explode(':', $value);
                if (count($s) == 1) {
                    return $s[0];
                }
            }

            return null;
        }

        return $search;
    }

    /**
     * @param array $fields
     * @param array|null $searchFields
     * @param array|null $dataKeys
     * @throws \Exception
     * @return array
     */
    protected function parserFieldsSearch(array $fields = [], array $searchFields = null, array $dataKeys = null): array {
        if (!is_null($searchFields) && count($searchFields)) {
            $acceptedConditions = config('repository.criteria.acceptedConditions', [
                '=',
                'like'
            ]);

            $originalFields = $fields;
            $fields = [];

            foreach ($searchFields as $index => $field) {
                $field_parts = explode(':', $field);
                $temporaryIndex = array_search($field_parts[0], $originalFields);

                if (count($field_parts) == 2) {
                    if (in_array($field_parts[1], $acceptedConditions)) {
                        unset($originalFields[$temporaryIndex]);
                        $field = $field_parts[0];
                        $condition = $field_parts[1];
                        $originalFields[$field] = $condition;
                        $searchFields[$index] = $field;
                    }
                }
            }

            if (!is_null($dataKeys) && count($dataKeys)) {
                $searchFields = array_unique(array_merge($dataKeys, $searchFields));
            }

            foreach ($originalFields as $field => $condition) {
                if (is_numeric($field)) {
                    $field = $condition;
                    $condition = "=";
                }
                if (in_array($field, $searchFields)) {
                    $fields[$field] = $condition;
                }
            }

            if (count($fields) == 0) {
                throw new \Exception(trans('repository::criteria.fields_not_accepted', ['field' => implode(',', $searchFields)]));
            }

        }

        return $fields;
    }
}
