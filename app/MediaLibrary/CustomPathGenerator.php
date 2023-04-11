<?php

namespace App\MediaLibrary;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class CustomPathGenerator implements PathGenerator
{
    /*
     * Get the path for the given media, relative to the root storage path.
     */
    public function getPath(Media $media): string
    {
        return $this->getBasePath($media);
    }

    /*
     * Get the path for conversions of the given media, relative to the root storage path.
     */
    public function getPathForConversions(Media $media): string {
        return $this->getBasePath($media) . 'conversions/';
    }

    /*
     * Get the path for responsive images of the given media, relative to the root storage path.
     */
    public function getPathForResponsiveImages(Media $media): string {
        return $this->getBasePath($media) . 'responsive-images/';
    }

    /*
     * Get a unique base path for the given media.
     */
    protected function getBasePath(Media $media): string {
        $this->setDirectoryPrefix(generate_dir_from_model_name($media->model_type));

        $prefix = config('media-library.prefix', '');

        if ($prefix !== '') {
            return "$prefix/$media->collection_name/$media->model_id/{$media->getKey()}/";
        }

        return $media->getKey();
    }

    /**
     * @param string $prefix
     */
    function setDirectoryPrefix(string $prefix): void {
        if ($prefix === 'Advertise') $prefix = 'Products';

        config(['media-library.prefix' => $prefix]);
    }
}
