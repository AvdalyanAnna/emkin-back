<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\AdvertiseStatistic
 *
 * @property int $id
 * @property int $type Счетчик кнопки с телефоном 0, Счетчик просмотра странички 1, Счетчик добавления в избранное 2
 * @property int $advertise_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertiseStatistic newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertiseStatistic newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertiseStatistic query()
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertiseStatistic whereAdvertiseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertiseStatistic whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertiseStatistic whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertiseStatistic whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertiseStatistic whereUpdatedAt($value)
 * @property int $user_id
 * @method static \Illuminate\Database\Eloquent\Builder|AdvertiseStatistic whereUserId($value)
 * @mixin \Eloquent
 */
class AdvertiseStatistic extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['type'];
}
