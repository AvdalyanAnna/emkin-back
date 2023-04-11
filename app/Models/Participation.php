<?php

namespace App\Models;

use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Chat\ConfigurationManager;

/**
 * Class Participation
 *
 * @package App\Models
 * @property int $id
 * @property int $conversation_id
 * @property int $messageable_id
 * @property string $messageable_type
 * @property array|null $settings
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Conversation $conversation
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $messageable
 * @method static \Illuminate\Database\Eloquent\Builder|Participation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Participation newQuery()
 * @method static \Illuminate\Database\Query\Builder|Participation onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Participation query()
 * @method static \Illuminate\Database\Eloquent\Builder|Participation whereConversationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Participation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Participation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Participation whereMessageableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Participation whereMessageableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Participation whereSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Participation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Participation withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Participation withoutTrashed()
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|Participation whereDeletedAt($value)
 * @property int $moderator_changed
 * @method static \Illuminate\Database\Eloquent\Builder|Participation whereModeratorChanged($value)
 * @mixin \Eloquent
 */
class Participation extends BaseModel
{
    use SoftDeletes;

    /**
     * @var string
     */
    protected $table = ConfigurationManager::PARTICIPATION_TABLE;

    /**
     * @var string[]
     */
    protected $fillable = [
        'conversation_id',
        'settings',
        'moderator_changed',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'settings' => 'array',
    ];

    /**
     * Conversation.
     *
     * @return BelongsTo
     */
    public function conversation(): BelongsTo {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    /**
     * @return MorphTo
     */
    public function messageable(): MorphTo {
        return $this->morphTo();
    }
}
