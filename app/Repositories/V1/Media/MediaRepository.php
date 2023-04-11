<?php

namespace App\Repositories\V1\Media;

use App\Models\Media;
use Prettus\Repository\Contracts\RepositoryInterface;
use Spatie\MediaLibrary\MediaCollections\Models\Media as MediaAlias;

/**
 * Interface MediaRepository
 * @package App\Repositories\V1
 */
interface MediaRepository extends RepositoryInterface
{

    /**
     * @param array $data
     * @param object $model
     * @param string $collection_name
     * @return mixed
     */
    public function addFile(array $data, object $model, string $collection_name): mixed;

    /**
     * @param Media $media
     * @return MediaAlias
     */
    public function duplicateFile(Media $media): MediaAlias;
}
