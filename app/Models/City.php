<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\City
 *
 * @method static \Illuminate\Database\Eloquent\Builder|City newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|City newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|City query()
 * @property int $id
 * @property string $name
 * @property string $state_code
 * @property string $latitude
 * @property string $longitude
 * @property int $state_id
 * @property int $country_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|City whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|City whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|City whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|City whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|City whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|City whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|City whereStateCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|City whereStateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|City whereUpdatedAt($value)
 * @property-read \App\Models\State $state
 * @property-read \App\Models\State $state_minimal_select
 * @property int $order
 * @method static \Illuminate\Database\Eloquent\Builder|City whereOrder($value)
 * @property-read \App\Models\Country $country
 * @mixin \Eloquent
 */
class City extends Model {

    use HasFactory;

    protected $fillable = [
        'name',
        'state_code',
        'latitude',
        'longitude',
        'order',
        'state_id',
        'country_id',
    ];

    /**
     * @return BelongsTo
     */
    public function state(): BelongsTo {
        return $this->belongsTo(State::class);
    }

    /**
     * @return BelongsTo
     */
    public function country(): BelongsTo {
        return $this->belongsTo(Country::class);
    }

    /**
     * @return BelongsTo
     */
    public function state_minimal_select(): BelongsTo {
        return $this->state()->select('id', 'name');
    }
}
