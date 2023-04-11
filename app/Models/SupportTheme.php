<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

/**
 * Class SupportTheme
 *
 * @package App\Models
 * @property int $id
 * @property string $title
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\SupportThemeFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|SupportTheme newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SupportTheme newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SupportTheme query()
 * @method static \Illuminate\Database\Eloquent\Builder|SupportTheme whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SupportTheme whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SupportTheme whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SupportTheme whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SupportTheme whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Ticket[] $tickets
 * @property-read int|null $tickets_count
 * @property int $order
 * @method static \Illuminate\Database\Eloquent\Builder|SupportTheme whereOrder($value)
 * @mixin \Eloquent
 */
class SupportTheme extends Model
{
    use HasFactory, HasTranslations;

    /**
     * @var string[]
     */
    protected $fillable = ['title', 'status', 'order'];

    /**
     * @var array|string[]
     */
    public array $translatable = [ 'title'];

    /**
     * @return HasMany
     */
    public function tickets(): HasMany {
        return $this->hasMany(Ticket::class);
    }
}
