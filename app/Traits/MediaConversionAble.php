<?php

namespace App\Traits;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Trait MediaConversionAble
 * @package App\Traits
 */
trait MediaConversionAble {

    /**
     * @param Media|null $media
     * @throws \Spatie\Image\Exceptions\InvalidManipulation
     */
    public function registerMediaConversions(Media $media = null): void
    {
        foreach ($media->custom_properties as $conversionName =>  $conversions) {
            $this->addMediaConversion($conversionName)
                ->width($conversions['width'])
                ->height($conversions['height'])
                ->format('webp')
                ->queued();

            $this->addMediaConversion("$conversionName-default")
                ->width($conversions['width'])
                ->height($conversions['height'])
                ->queued();
        }
    }
}
