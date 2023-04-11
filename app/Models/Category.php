<?php

namespace App\Models;

use App\Traits\ActiveGlobalScopeAble;
use App\Traits\MediaConversionAble;
use App\Traits\SlugAble;
use Bkwld\Cloner\Cloneable;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

/**
 * Class Category
 *
 * @package App\Models
 * @property-read \Illuminate\Database\Eloquent\Collection|Category[] $subCategory
 * @property-read int|null $sub_category_count
 * @method static \Illuminate\Database\Eloquent\Builder|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Category newQuery()
 * @method static \Illuminate\Database\Query\Builder|Category onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Category query()
 * @method static \Illuminate\Database\Query\Builder|Category withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Category withoutTrashed()
 * @property int $id
 * @property array $name
 * @property string $slug
 * @property int|null $parent_id
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Category|null $category
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection|\App\Models\Media[] $media
 * @property-read int|null $media_count
 * @property-read \App\Models\Media|null $picture
 * @property-read \Illuminate\Database\Eloquent\Collection|Category[] $subCategories
 * @property-read int|null $sub_categories_count
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|Category[] $allSubCategories
 * @property-read int|null $all_sub_categories_count
 * @property int $status
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereStatus($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Advertise[] $advertises
 * @property-read int|null $advertises_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Filter[] $filters
 * @property-read int|null $filters_count
 * @method static \Database\Factories\CategoryFactory factory(...$parameters)
 * @property int $order
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereOrder($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|Category[] $parentCategories
 * @property-read int|null $parent_categories_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Category> $allSubCategoriesBySelect
 * @property-read int|null $all_sub_categories_by_select_count
 * @mixin \Eloquent
 */
class Category extends Model implements HasMedia {

    use HasFactory, SlugAble, SoftDeletes, CascadeSoftDeletes, Cloneable,
        ActiveGlobalScopeAble, InteractsWithMedia, HasTranslations, MediaConversionAble {
        MediaConversionAble::registerMediaConversions insteadof InteractsWithMedia;
    }

    /**
     * @var array|string[]
     */
    protected array $cascadeDeletes = ['subCategories'];

    /**
     * @var string[]
     */
    public array $translatable = ['name'];

    /**
     * @var array
     */
    protected $relations = ['subCategories', 'picture', 'category'];

    /**
     * @var array|string[]
     */
    protected array $cloneable_relations = ['filters'];

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
        'id',
        'name',
        'slug',
        'status',
        'order',
        'parent_id',
        'deleted_at',
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
     * @return HasMany
     */
    public function allSubCategories(): HasMany {
        return $this->hasMany(Category::class, 'parent_id')
            ->with('allSubCategories')->orderBy('order', 'ASC');
    }

    /**
     * @return HasMany
     */
    public function allSubCategoriesBySelect(): HasMany {
        return $this->hasMany(Category::class, 'parent_id')
            ->select(['id', 'parent_id'])
            ->with('allSubCategories:id,parent_id');
    }

    /**
     * @return HasMany
     */
    public function subCategories(): HasMany {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * @return HasMany
     */
    public function advertises(): HasMany {
        return $this->hasMany(Advertise::class);
    }

    /**
     * @return HasMany
     */
    public function filters(): HasMany {
        return $this->hasMany(Filter::class);
    }

    /**
     * @return BelongsTo
     */
    public function category(): BelongsTo {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * @return HasMany
     */
    public function parentCategories(): HasMany {
        return $this
            ->hasMany(Category::class, 'id', 'parent_id')
            ->with('parentCategories:id,parent_id,slug,name');
    }

    /**
     * @return MorphOne
     */
    public function picture(): MorphOne {
        return $this->morphOne(config('media-library.media_model'), 'model');
    }
}
