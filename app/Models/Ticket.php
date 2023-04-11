<?php

namespace App\Models;

use App\Enums\MediaCollections;
use App\Traits\TimezoneChangeAble;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Class Ticket
 *
 * @package App\Models
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $description
 * @property int $status
 * @property int $guest
 * @property int $support_theme_id
 * @property int|null $user_id
 * @property int|null $moderator_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Media|null $files
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection|\App\Models\Media[] $media
 * @property-read int|null $media_count
 * @property-read \App\Models\User|null $moderator
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket query()
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereGuest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereModeratorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereSupportThemeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereUserId($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @property-read int|null $files_count
 * @property-read \App\Models\Conversation $conversation
 * @property-read string|null $humans_time
 * @mixin \Eloquent
 */
class Ticket extends Model implements HasMedia, Auditable
{
    use HasFactory, InteractsWithMedia, \OwenIt\Auditing\Auditable, TimezoneChangeAble;

    /**
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'description',
        'status',
        'support_theme_id',
        'user_id',
        'moderator_id',
    ];

    /**
     * @return MorphMany
     */
    public function files(): MorphMany {
        return $this->media()->where('collection_name', '=', MediaCollections::SUPPORT_TICKET_FILE);
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo
     */
    public function moderator(): BelongsTo {
        return $this->belongsTo(User::class, 'moderator_id');
    }

    /**
     * @return HasOne
     */
    public function conversation(): HasOne {
        return $this->hasOne(Conversation::class, 'ticket_id');
    }

    /**
     * // TODO avelacnel traiti mech
     * @return string|null
     */
    public function getHumansTimeAttribute(): string|null {
        $date = $this->created_at;

        if (!$date) return null;

        $now = $date->now();

        return $date->diffForHumans($now, true);
    }
}
