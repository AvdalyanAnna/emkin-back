<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

/**
 * App\Models\Refusal
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $refusal
 * @method static \Illuminate\Database\Eloquent\Builder|Refusal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Refusal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Refusal query()
 * @method static \Illuminate\Database\Eloquent\Builder|Refusal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Refusal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Refusal whereRefusal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Refusal whereUpdatedAt($value)
 * @property int $type Тип 0 это Виды Текста Для Модераторов когда отклоняют объявление, Тип 1 Это типы Жалоб Для Чата Пользователей
 * @method static \Illuminate\Database\Eloquent\Builder|Refusal whereType($value)
 * @property int $order
 * @method static \Illuminate\Database\Eloquent\Builder|Refusal whereOrder($value)
 * @mixin \Eloquent
 */
class Refusal extends Model
{
    use HasFactory, HasTranslations;

    /**
     * @var string[]
     */
    protected $fillable = ['refusal', 'type', 'order'];

    /**
     * @var array|string[]
     */
    public array $translatable = [ 'refusal'];
}
