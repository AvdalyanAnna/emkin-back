<?php

namespace App\Models;

use App\Enums\MediaCollections;
use App\Traits\TimezoneChangeAble;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Class Complaint
 *
 * @package App\Models
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection|\App\Models\Media[] $files
 * @property-read int|null $files_count
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection|\App\Models\Media[] $media
 * @property-read int|null $media_count
 * @property-read \App\Models\Refusal $reason_for_refusal
 * @method static \Illuminate\Database\Eloquent\Builder|Complaint newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Complaint newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Complaint query()
 * @property-read \App\Models\Conversation|null $conversation
 * @property-read \App\Models\User $user
 * @property int $id
 * @property string|null $description
 * @property string $email
 * @property int|null $refusal_id
 * @property int|null $conversation_id
 * @property int|null $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Complaint whereConversationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Complaint whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Complaint whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Complaint whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Complaint whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Complaint whereRefusalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Complaint whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Complaint whereUserId($value)
 * @mixin \Eloquent
 */
class Complaint extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, TimezoneChangeAble;

    /**
     * @var string[]
     */
    protected $fillable = [
        'description',
        'refusal_id',
        'conversation_id',
        'user_id',
    ];

    /**
     * @return MorphMany
     */
    public function files(): MorphMany {
        return $this->media()->where('collection_name', '=', MediaCollections::CHAT_COMPLAINT_FILE);
    }

    /**
     * @return BelongsTo
     */
    public function reason_for_refusal(): BelongsTo {
        return $this->belongsTo(Refusal::class,'refusal_id');
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
    public function conversation(): BelongsTo {
        return $this->belongsTo(Conversation::class);
    }
}
