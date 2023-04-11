<?php

namespace App\Models;

use App\Traits\MediaConversionAble;
use App\Traits\SlugAble;
use App\Traits\TimezoneChangeAble;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Class Advertise
 *
 * @package App\Models
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\FilterAnswer[] $answers
 * @property-read int|null $answers_count
 * @property-read \App\Models\User|null $author
 * @property-read \App\Models\Category|null $category
 * @property-read \App\Models\City|null $city
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection|\App\Models\Media[] $gallery
 * @property-read int|null $gallery_count
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection|\App\Models\Media[] $media
 * @property-read int|null $media_count
 * @method static \Illuminate\Database\Eloquent\Builder|Advertise newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Advertise newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Advertise query()
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property float|null $price
 * @property string $latitude
 * @property string $longitude
 * @property string|null $link
 * @property int $type 0 Это простая обновления а 1 это VIP
 * @property int $status Не проверено 0,  Активно 1, Не активно 2, Отклонено 3, Забанено 4
 * @property string|null $contacts
 * @property int|null $available_cost
 * @property int|null $show_phone
 * @property int|null $show_details
 * @property int|null $added_favorites
 * @property int $user_id
 * @property int $category_id
 * @property int $city_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $inactively_date
 * @property int|null $auto-renewal
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AdvertiseStatistic[] $statistics
 * @property-read int|null $statistics_count
 * @method static \Illuminate\Database\Eloquent\Builder|Advertise whereAddedFavorites($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Advertise whereAutoRenewal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Advertise whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Advertise whereCityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Advertise whereContactMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Advertise whereContactPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Advertise whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Advertise whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Advertise whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Advertise whereInactivelyDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Advertise whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Advertise whereLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Advertise whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Advertise whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Advertise wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Advertise whereShowDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Advertise whereShowPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Advertise whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Advertise whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Advertise whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Advertise whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Advertise whereUserId($value)
 * @property int $id
 * @property string $address
 * @property int|null $price_policy Ценовая Политика объявление. бесплатное 0,  платное 1, обмен 3
 * @property mixed reject_message
 * @method static \Illuminate\Database\Eloquent\Builder|Advertise whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Advertise whereAvailableCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Advertise wherePricePolicy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Advertise whereRejectMessage($value)
 * @property int|null $refusal_id
 * @method static \Illuminate\Database\Eloquent\Builder|Advertise whereRefusalId($value)
 * @property int $auto_renewal
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AdvertiseStatistic[] $favorites
 * @property-read int|null $favorites_count
 * @property-read string $humans_time
 * @method static \Database\Factories\AdvertiseFactory factory(...$parameters)
 * @property Carbon|null $deleted_at
 * @property-read \App\Models\Refusal|null $reason_for_refusal
 * @method static \Illuminate\Database\Query\Builder|Advertise onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Advertise whereDeletedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Advertise withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Advertise withoutTrashed()
 * @property string|null $refusal_comment
 * @method static \Illuminate\Database\Eloquent\Builder|Advertise whereRefusalComment($value)
 * @property string|null $contact_phone
 * @property string|null $contact_message
 * @property string|null $filters
 * @property int|null $contact_phone_numeric
 * @method static \Illuminate\Database\Eloquent\Builder|Advertise whereContactPhoneNumeric($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Advertise whereContacts($value)
 * @mixin \Eloquent
 */
class Advertise extends Model implements HasMedia {

    use HasFactory, TimezoneChangeAble,  SlugAble, InteractsWithMedia, InteractsWithMedia,
        SoftDeletes, CascadeSoftDeletes, MediaConversionAble {
        MediaConversionAble::registerMediaConversions insteadof InteractsWithMedia;
    }

    /**
     * @var array
     */
    protected $relations = ['category', 'author', 'gallery', 'city', 'answers'];

    /**
     * @var array|string[]
     */
    protected array $cascadeDeletes = ['gallery'];

    /**
     * @var string $slugName
     */
    public static string $slugName = 'name';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'filters',
        'description',
        'price',
        'address',
        'latitude',
        'longitude',
        'link',
        'auto_renewal',
        'type',
        'status',
        'available_cost',
        'price_policy',
        'show_phone',
        'show_details',
        'added_favorites',
        'contacts',
        'contact_phone',
        'contact_phone_numeric',
        'refusal_comment',
        'user_id',
        'category_id',
        'city_id',
        'refusal_id',
        'inactively_date',
        'created_at',
        'updated_at',
    ];

    /**
     * @return string
     */
    public function getRouteKeyName(): string {
        return 'slug';
    }

    /**
     * @return BelongsTo
     */
    public function category(): BelongsTo {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo
     */
    public function author(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo
     */
    public function city(): BelongsTo {
        return $this->belongsTo(City::class);
    }

    /**
     * @return BelongsToMany
     */
    public function answers(): BelongsToMany {
        return $this->belongsToMany(FilterAnswer::class,'advertise_answers');
    }

    /**
     * @return BelongsTo
     */
    public function reason_for_refusal(): BelongsTo {
        return $this->belongsTo(Refusal::class,'refusal_id');
    }

    /**
     * @return MorphMany
     */
    public function gallery(): MorphMany {
        return $this->media()
            ->where('collection_name', '=', \App\Enums\MediaCollections::ADVERTISE_COLLECTION)
            ->orderBy('order_column', 'ASC');
    }

    /**
     * @return HasMany
     */
    public function statistics(): HasMany {
        return $this->hasMany(AdvertiseStatistic::class);
    }

    /**
     * @return HasMany
     */
    public function favorites(): HasMany {
        return $this->statistics()->where('type', '=', 2);
    }

    /**
     * // TODO avelacnel traiti mech
     * @return string
     */
    public function getHumansTimeAttribute(): string {
        $date = $this->created_at;
        $now = $date->now();

        return $date->diffForHumans($now, true);
    }

    /**
     * @param array $value
     * @return string
     */
    public function setFiltersAttribute(array $value): string {
        $filters = '';
        foreach ($value as $answer) $filters .= "[$answer]";
        return $this->attributes['filters'] = $filters;
    }
}
