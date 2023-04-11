<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\UserSearch
 *
 * @property int $id
 * @property string $search
 * @property int $search_count
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|UserSearch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserSearch newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserSearch query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserSearch whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSearch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSearch whereSearch($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSearch whereSearchCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserSearch whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class UserSearch extends Model
{
    use HasFactory;
}
