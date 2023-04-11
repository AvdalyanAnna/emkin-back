<?php

namespace App\Support\Macros;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class CreateUpdateOrDeleteAnswers
 * @package App\Support\Macros
 */
class CreateUpdateOrDeleteAnswers {

    /**
     * @var HasMany
     */
    protected HasMany $query;

    /**
     * @var Collection
     */
    protected Collection $records;

    /**
     * @var object $model
     */
    protected object $model;

    /**
     * CreateUpdateOrDeleteAnswers constructor.
     * @param HasMany $query
     * @param iterable $records
     * @param object $model
     */
    public function __construct(HasMany $query, iterable $records, object $model) {
        $this->query = $query;
        $this->model = $model;
        $this->records = collect($records);
    }

    /**
     * @throws \Throwable
     */
    public function __invoke() {
        DB::transaction(function () {
            $this->deleteMissingRecords();
            $this->upsertRecords();
        });
    }

    protected function deleteMissingRecords() :void
    {
        $recordKeyName = $this->query->getRelated()->getKeyName();

        $existingRecordIds = $this->records->pluck($recordKeyName)->filter();

        (clone $this->query)->whereNotIn($recordKeyName, $existingRecordIds)->delete();
    }

    protected function upsertRecords() {
        $values = $this->records->map(function ($record) {
            // Установите $record['recipe_id'] в качестве родительского ключа.
            $record[$this->query->getForeignKeyName()] = $this->query->getParentKey();

            // Установите для $record['id'] значение null при отсутствии.
            $recordKeyName = $this->query->getRelated()->getKeyName();

            if (array_key_exists($recordKeyName, $record)) {
                $answers = $this->model->answers->where('id', $record[$recordKeyName])->first();
                $name = $answers->getTranslations('name');
                $name[app()->getLocale()] = $record['name'];
            } else {
                $record[$recordKeyName] = null;
                $name = [app()->getLocale() => $record['name']];
            }

            $record['name'] = json_encode($name, true);

            return $record;
        })->toArray();

        (clone $this->query)->upsert($values, [$this->query->getRelated()->getKeyName()]);
    }
}
