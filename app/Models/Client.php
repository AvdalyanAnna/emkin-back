<?php

namespace App\Models;

use App\Enums\Admin\Commercial\CommercialStatuses;
use App\Traits\MediaConversionAble;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Class Client
 *
 * @package App\Models
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $phone
 * @property string $company
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Media|null $avatar
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CommercialBusiness[] $businesses
 * @property-read int|null $businesses_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CommercialBusiness[] $canceled_publications
 * @property-read int|null $canceled_publications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CommercialBusiness[] $commercial_businesses
 * @property-read int|null $commercial_businesses_count
 * @property-read string $full_name
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection|\App\Models\Media[] $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CommercialBusiness[] $publications
 * @property-read int|null $publications_count
 * @method static \Illuminate\Database\Eloquent\Builder|Client newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Client newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Client query()
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereCompany($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Client whereUpdatedAt($value)
 * @property string|null $phone_view
 * @method static \Illuminate\Database\Eloquent\Builder|Client wherePhoneView($value)
 * @mixin \Eloquent
 */
class Client extends Model implements HasMedia {

    use HasFactory,InteractsWithMedia, MediaConversionAble {
        MediaConversionAble::registerMediaConversions insteadof InteractsWithMedia;
    }

    /**
     * @var string[]
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'phone_view',
        'company',
        'status',
    ];

    /**
     * Add a mutator to ensure hashed passwords
     */
    public function getFullNameAttribute(): string {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * @return HasMany
     */
    public function commercial_businesses(): HasMany {
        return $this->hasMany(CommercialBusiness::class);
    }

    /**
     * @return MorphOne
     */
    public function avatar(): MorphOne {
        return $this
            ->morphOne(config('media-library.media_model'), 'model')
            ->where('collection_name', '=', \App\Enums\MediaCollections::COMMERCIAL_CLIENT_AVATAR);
    }

    /**
     * @return HasMany
     */
    public function businesses(): HasMany {
        return $this->hasMany(CommercialBusiness::class);
    }

    /**
     * @return HasMany
     */
    public function publications(): HasMany {
        return $this->hasMany(CommercialBusiness::class)->where('status', CommercialStatuses::ACTIVE);
    }

    /**
     * @return HasMany
     */
    public function canceled_publications(): HasMany {
        return $this->hasMany(CommercialBusiness::class)->where('status', CommercialStatuses::CLOSED);
    }
}
