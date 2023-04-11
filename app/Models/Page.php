<?php

namespace App\Models;

use App\Traits\MediaConversionAble;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

/**
 * Class Page
 *
 * @package App\Models
 * @property int $id
 * @property string $locale
 * @property string $type
 * @property string|null $name
 * @property array $content
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection|\App\Models\Media[] $backgrounds
 * @property-read int|null $backgrounds_count
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection|\App\Models\Media[] $media
 * @property-read int|null $media_count
 * @method static \Illuminate\Database\Eloquent\Builder|Page newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Page newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Page query()
 * @method static \Illuminate\Database\Eloquent\Builder|Page whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Page whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Page whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Page whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Page whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Page whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Page whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Page whereUpdatedAt($value)
 * @property string $page_key
 * @method static \Illuminate\Database\Eloquent\Builder|Page wherePageKey($value)
 * @mixin \Eloquent
 */
class Page extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, InteractsWithMedia, MediaConversionAble, HasTranslations {
        MediaConversionAble::registerMediaConversions insteadof InteractsWithMedia;
    }

    /**
     * @var array
     */
    protected $relations = ['backgrounds'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'locale',
        'name',
        'type',
        'status',
        'page_key',
        'content',
        'created_at',
        'updated_at',
    ];

    /**
     * @var array|string[]
     */
    public array $translatable = [ 'content'];

    /**
     * @var string[]
     */
    protected $casts = ['content' => 'array'];

    /**
     * @return MorphMany
     */
    public function backgrounds(): MorphMany {
        return $this->media()->where('collection_name', '=', \App\Enums\MediaCollections::BACKGROUND_COLLECTION);
    }
}
