<?php

namespace App\Models;

use App\Traits\OrderGlobalScopeAble;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

/**
 * Class FilterAnswer
 *
 * @package App\Models
 * @property int $id
 * @property array $name
 * @property int $order
 * @property int|null $filter_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Filter|null $filter
 * @method static \Illuminate\Database\Eloquent\Builder|FilterAnswer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FilterAnswer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FilterAnswer query()
 * @method static \Illuminate\Database\Eloquent\Builder|FilterAnswer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FilterAnswer whereFilterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FilterAnswer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FilterAnswer whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FilterAnswer whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FilterAnswer whereUpdatedAt($value)
 * @method static \Database\Factories\FilterAnswerFactory factory(...$parameters)
 * @mixin \Eloquent
 */
class FilterAnswer extends Model
{
    use HasFactory, HasTranslations, OrderGlobalScopeAble;

    /**
     * @var string[]
     */
    public array $translatable = ['name'];

    /**
     * @var string $slugName
     */
    public static string $slugName = 'name';

    /**
     * @var array
     */
    protected $relations = ['filter'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'order',
        'filter_id'
    ];

    /**
     * @return BelongsTo
     */
    public function filter(): BelongsTo {
        return $this->belongsTo(Filter::class);
    }
}
