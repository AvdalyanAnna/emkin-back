<?php

namespace App\Models;

use App\Enums\MediaCollections;
use App\Traits\MediaConversionAble;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

/**
 * Class CommercialBusiness
 *
 * @package App\Models
 * @property int $id
 * @property string $name
 * @property string $type указываем только превьюшку и опциями правим внешний вид (Banner Constructor) type => 0, когда указываем полностью изображение баннера (Custom). type => 1
 * @property string $link
 * @property string|null $location
 * @property array $details
 * @property int $status
 * @property int $client_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Media|null $banner_image
 * @property-read \App\Models\Client $client
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection|\App\Models\Media[] $media
 * @property-read int|null $media_count
 * @method static \Illuminate\Database\Eloquent\Builder|CommercialBusiness newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CommercialBusiness newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CommercialBusiness query()
 * @method static \Illuminate\Database\Eloquent\Builder|CommercialBusiness whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommercialBusiness whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommercialBusiness whereDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommercialBusiness whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommercialBusiness whereLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommercialBusiness whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommercialBusiness whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommercialBusiness whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommercialBusiness whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommercialBusiness whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CommercialBusiness whereBannerHorizontal($value)
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection|\App\Models\Media[] $banner_images
 * @property-read int|null $banner_images_count
 * @property int $banner_horizontal 0 Vertical 1 horizontal
 * @mixin \Eloquent
 */
class CommercialBusiness extends Model implements HasMedia {

    use HasFactory, InteractsWithMedia, MediaConversionAble, HasTranslations {
        MediaConversionAble::registerMediaConversions insteadof InteractsWithMedia;
    }

    /**
     * @var string[]
     */
    protected $fillable = [
        'name',
        'location',
        'link',
        'type',
        'status',
        'details',
        'client_id',
    ];

    /**
     * @var array|string[]
     */
    public array $translatable = ['name', 'details'];

    /**
     * @return MorphMany
     */
    public function banner_images(): MorphMany {
        return $this->media()
            ->where('collection_name', '=', MediaCollections::COMMERCIAL_BUSINESS_BANNER);
    }

    /**
     * @return BelongsTo
     */
    public function client(): BelongsTo {
        return $this->belongsTo(Client::class);
    }
}
