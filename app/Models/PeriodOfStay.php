<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

/**
 * Class PeriodOfStay
 *
 * @package App\Models
 * @property int $id
 * @property array $title
 * @property int $count_days
 * @property int $order
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodOfStay newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodOfStay newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodOfStay query()
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodOfStay whereCountDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodOfStay whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodOfStay whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodOfStay whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodOfStay whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodOfStay whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PeriodOfStay whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PeriodOfStay extends Model
{
    use HasFactory, HasTranslations;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'status',
        'order',
    ];

    /**
     * @var array|string[]
     */
    public array $translatable = [ 'title'];
}
