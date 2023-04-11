<?php

namespace App\Models;

use App\Enums\MediaCollections;
use App\Traits\MediaConversionAble;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

/**
 * Class CommercialNotification
 *
 * @package App\Models
 * @property int $id
 * @property int $text
 * @property string $title
 * @property string $description
 * @property string $link
 * @property array|null $details
 * @property int $type Draft 0, Banner 1, Notification 2
 * @property int $status Not Active 0, Active 1
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Media|null $banner_image
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection|\App\Models\Media[] $media
 * @property-read int|null $media_count
 * @method static \Illuminate\Database\Eloquent\Builder|CommercialNotification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CommercialNotification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CommercialNotification query()
 * @method static \Illuminate\Database\Eloquent\Builder|CommercialNotification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommercialNotification whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommercialNotification whereDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommercialNotification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommercialNotification whereLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommercialNotification whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommercialNotification whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommercialNotification whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommercialNotification whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommercialNotification whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CommercialNotification extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, HasTranslations, MediaConversionAble {
        MediaConversionAble::registerMediaConversions insteadof InteractsWithMedia;
    }

    /**
     * @var string[]
     */
    protected $fillable = [
        'text',
        'title',
        'description',
        'link',
        'type',
        'status',
        'details',
    ];

    /**
     * @var string[]
     */
    public array $translatable = ['title', 'description', 'details'];

    /**
     * @return MorphOne
     */
    public function banner_image(): MorphOne {
        return $this
            ->morphOne(config('media-library.media_model'), 'model')
            ->where('collection_name', '=', MediaCollections::COMMERCIAL_NOTIFICATION_BANNER);
    }
}
