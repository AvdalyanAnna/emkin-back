<?php

namespace App\Repositories\V1\Media;

use App\Models\Media;
use App\Traits\UploadAble;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Validator\Exceptions\ValidatorException;
use Spatie\MediaLibrary\MediaCollections\Models\Media as MediaAlias;

/**
 * Class AdvertiseRepositoryEloquent
 * @package App\Repositories\V1\Admin
 */
class MediaRepositoryEloquent extends BaseRepository implements MediaRepository {

    use UploadAble;

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model(): string {
        return \App\Models\Media::class;
    }

    /**
     * @param $attributes
     * @param Media $media
     * @throws ValidatorException
     * @return mixed
     */
    public function updateMedia($attributes, Media $media): mixed {
        return parent::update([
            'custom_details' => $attributes
        ], $media->id);
    }

    /**
     * @param $data
     * @param object $model
     * @param string $collection_name
     * @return mixed
     */
    public function addFile($data, object $model, string $collection_name): mixed {

        if (isset($data['files'])) {
            $files = [];

            foreach ($data['files'] as $file) {
                $newFile = $this->addResponsiveImageWithCustomDetails(
                    $model,
                    $file,
                    $collection_name,
                    [],
                    $data['custom_details'] ?? [],
                    $this->model()::DEFAULT_DISC
                );
                array_push($files, $newFile);
            }

            return $files;
        }

        return $this->addResponsiveImageWithCustomDetails(
            $model,
            $data['file'],
            $collection_name,
            [],
            $data['custom_details'] ?? [],
            $this->model()::DEFAULT_DISC
        );
    }

    /**
     * @param Media $media
     * @return MediaAlias
     */
    public function duplicateFile(Media $media): MediaAlias {
        $currentMediaModel = (new $media->model_type)->find($media->model_id);
        return $media->copy($currentMediaModel, $media->collection_name, $media::DEFAULT_DISC);
    }
}
