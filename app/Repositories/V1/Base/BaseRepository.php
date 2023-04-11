<?php

namespace App\Repositories\V1\Base;

use App\Enums\MediaCollections;
use App\Models\Media;
use App\Repositories\V1\Media\MediaRepository;
use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Models\Media as MediaAlias;

/**
 * Class BaseRepository
 * @package App\Repositories\V1\Base
 */
abstract class BaseRepository extends \Prettus\Repository\Eloquent\BaseRepository implements BaseContract {

    /**
     * Specify Model class name
     *
     * @return string
     */
    abstract public function model(): string;

    /**
     * @param string|array $scopes
     * @param array $where
     * @param array|string[] $columns
     * @throws RepositoryException
     * @return mixed
     */
    public function withoutGlobalScopes(string|array $scopes, array $where = [], array $columns = ['*']): mixed {
        $this->applyCriteria();
        $this->applyScope();
        $model = $this->model->withoutGlobalScope($scopes)->where($where);
        $this->resetModel();
        return $this->parserResult($model);
    }

    /**
     * @param array $columns
     * @throws RepositoryException
     * @return mixed
     */
    public function firstOrFile(array $columns = ['*']): mixed {

        $this->applyCriteria();
        $this->applyScope();

        $results = $this->model->firstOrFile($columns);

        $this->resetModel();

        return $this->parserResult($results);
    }

    /**
     * Find data by multiple fields
     *
     * @param array $where
     * @param array $columns
     * @param null $limit
     * @throws RepositoryException
     * @return mixed
     */
    public function findWherePaginate(array $where, array $columns = ['*'], $limit = null): mixed {
        $this->applyCriteria();
        $this->applyScope();

        $this->applyConditions($where);
        $limit = is_null($limit) ? config('repository.pagination.limit', 15) : $limit;
        $results = $this->model->paginate($limit, $columns);
        $results->appends(app('request')->query());
        $this->resetModel();

        return $this->parserResult($results);
    }

    /**
     * @param array $attributes
     * @throws ValidatorException|FileDoesNotExist
     * @return Model
     */
    public function updateOrCreateModel(array $attributes = []): Model {
        $model = $this->updateOrCreate([
            'id' => $attributes['id'] ?? null
        ], $attributes);

        if (isset($attributes['files']) && $files = $attributes['files'])
            foreach ($files as $file) $this->setExistsMediaFileToModel($model, $file);

        if ($this->existsCollectionName()) {
            if (existsUploadAbleFileInArray($attributes)) {
                $model->clearMediaCollection($this->collection_name);
                $this->addResponsiveImage($model, $attributes['file'], $this->collection_name);
            }
        }

        return $model;
    }

    /**
     * @param HasMedia $model
     * @param array $file
     * @param bool $delete_old_media
     * @param array $oldMediaIds
     * @param array $custom_details
     * @return bool|object
     */
    public function setExistsMediaFileToModel(
        HasMedia $model,
        array $file = [],
        bool $delete_old_media = false,
        array $oldMediaIds = [],
        array $custom_details = []
    ): bool|MediaAlias {
        if (property_exists($this, 'collection_name')) {
            if (!empty($file)) {
                $mediaRepository = app(MediaRepository::class);
                $newFile = $mediaRepository->find($file['media_id']);

                if ($delete_old_media) {
                    if (empty($oldMediaIds)) {
                        $oldFile = $model->getFirstMedia(MediaCollections::getCollectionNameByModelType($model));

                        if ($oldFile) {
                            $old_collection_name = $oldFile->collection_name;
                            $oldFile->forceDelete();
                        }
                    } else {
                        $oldFiles = $mediaRepository->findWhereIn('id', $oldMediaIds);
                        $old_collection_name = $oldFiles[0]->collection_name;

                        $oldFiles->map(function ($item) {
                            $item->forceDelete();
                        });
                    }
                }

                $newFile = $newFile->copy(
                    $model,
                    $old_collection_name ?? MediaCollections::getCollectionNameByModelType($model),
                    Media::DEFAULT_DISC
                );

                $cloneFile = $newFile;

                unset($file['media_id']);
                $newFile->withCustomDetails($file);

                if(isset($file['image_key'])) {
                    $newFile->update(['image_key' => $file['image_key']]);
                } else {
                    $newFile->update();
                }
            }

            return $cloneFile ?? false;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function existsCollectionName(): bool {
        return property_exists($this, 'collection_name');
    }
}
