<?php

namespace App\Repositories\V1\Base;

use Prettus\Repository\Contracts\RepositoryInterface;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media as MediaAlias;

/**
 * Interface BaseContract
 * @package App\Repositories\V1\Base
 */
interface BaseContract extends RepositoryInterface {

    /**
     * @param string|array $scopes
     * @param array $where
     * @param array|string[] $columns
     * @return mixed
     */
    public function withoutGlobalScopes(string|array $scopes, array $where = [], array $columns = ['*']): mixed;

    /**
     * @param array $columns
     * @return mixed
     */
    public function firstOrFile(array $columns = ['*']): mixed;

    /**
     * Find data by multiple fields
     *
     * @param array $where
     * @param array $columns
     * @param null $limit
     * @return mixed
     */
    public function findWherePaginate(array $where, array $columns = ['*'], $limit = null): mixed;

    /**
     * Find data by multiple fields
     *
     * @param array $attributes
     * @return mixed
     */
    public function updateOrCreateModel(array $attributes): mixed;

    /**
     * Find data by multiple fields
     *
     * @param HasMedia $model
     * @param array $file
     * @param bool $delete_old_media
     * @param array $oldMediaIds
     * @param array $custom_details
     * @return bool|MediaAlias;
     */
    public function setExistsMediaFileToModel(
        HasMedia $model,
        array $file = [],
        bool $delete_old_media = false,
        array $oldMediaIds = [],
        array $custom_details = []
    ): bool|MediaAlias;
}
