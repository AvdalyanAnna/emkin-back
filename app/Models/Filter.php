<?php

namespace App\Models;

use App\Traits\SlugAble;
use Bkwld\Cloner\Cloneable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

/**
 * Class Filter
 *
 * @package App\Models
 * @property int $id
 * @property array $name
 * @property string $slug
 * @property int $order
 * @property int|null $category_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\FilterAnswer[] $answers
 * @property-read int|null $answers_count
 * @property-read \App\Models\Category|null $category
 * @method static \Illuminate\Database\Eloquent\Builder|Filter newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Filter newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Filter query()
 * @method static \Illuminate\Database\Eloquent\Builder|Filter whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Filter whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Filter whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Filter whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Filter whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Filter whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Filter whereUpdatedAt($value)
 * @method static \Database\Factories\FilterFactory factory(...$parameters)
 * @mixin \Eloquent
 */
class Filter extends Model {

    use HasFactory, HasTranslations, SlugAble, Cloneable;

    /**
     * @var string[]
     */
    public array $translatable = ['name'];

    /**
     * @var array
     */
    protected $relations = ['answers', 'category'];

    /**
     * @var array|string[]
     */
    protected array $cloneable_relations = ['answers'];

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
        'order',
        'category_id'
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
    public function answers(): HasMany {
        return $this->hasMany(FilterAnswer::class);
    }

    /**
     * @return BelongsTo
     */
    public function category(): BelongsTo {
        return $this->belongsTo(Category::class);
    }
}
