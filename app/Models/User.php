<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Chat\ConfigurationManager;
use App\Enums\Advertise\AdvertiseStatistic as AdvertiseStatisticAlias;
use App\Enums\Advertise\AdvertiseStatus;
use App\Enums\Users\SubscriptionStatuses;
use App\Traits\MediaConversionAble;
use Carbon\Carbon;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\Chat\Messageable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Traits\HasRoles;

/**
 * App\Models\User
 *
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Sanctum\PersonalAccessToken[] $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @property int $id
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $email
 * @property string|null $phone
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string|null $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string $full_name
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @property string|null $verified_at
 * @method static \Illuminate\Database\Eloquent\Builder|User whereVerifiedAt($value)
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection|\Spatie\MediaLibrary\MediaCollections\Models\Media[] $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Permission[] $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Role[] $roles
 * @property-read int|null $roles_count
 * @method static Builder|User permission($permissions)
 * @method static Builder|User role($roles, $guard = null)
 * @property int|null $city_id
 * @method static Builder|User whereCityId($value)
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection|Media[] $avatar
 * @property-read int|null $avatar_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AdvertiseStatistic[] $favorites
 * @property-read int|null $favorites_count
 * @property int $banned
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Advertise[] $advertises
 * @property-read int|null $advertises_count
 * @property-read \Illuminate\Database\Eloquent\Collection|User[] $block_list
 * @property-read int|null $block_list_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $unread_notifications
 * @property-read int|null $unread_notifications_count
 * @method static Builder|User isNotBanned()
 * @method static Builder|User whereBanned($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Subscription[] $subscription
 * @property-read int|null $subscription_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Ticket[] $moderator_tickets
 * @property-read int|null $moderator_tickets_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Ticket[] $tickets_user
 * @property-read int|null $tickets_user_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Advertise[] $advertise_favorites
 * @property-read int|null $advertise_favorites_count
 * @property-read string $registered
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Participation[] $participation
 * @property-read int|null $participation_count
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read array $advertise_favorites_ids
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SocialAccount[] $social_accounts
 * @property-read int|null $social_accounts_count
 * @method static \Illuminate\Database\Query\Builder|User onlyTrashed()
 * @method static Builder|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Query\Builder|User withTrashed()
 * @method static \Illuminate\Database\Query\Builder|User withoutTrashed()
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Conversation[] $conversations
 * @property-read int|null $conversations_count
 * @property-read array $permissions_ids
 * @property string|null $phone_view
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Advertise[] $canceled_advertises
 * @property-read int|null $canceled_advertises_count
 * @method static Builder|User wherePhoneView($value)
 * @method static Builder|User moderatorCan(string $permission)
 * @property-read \App\Models\City|null $city
 * @property-read \App\Models\Country $country
 * @property int|null $country_id
 * @method static Builder|User whereCountryId($value)
 * @method static Builder|User whereCountry($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable implements HasMedia {

    use HasApiTokens, HasFactory, Notifiable,
        InteractsWithMedia, HasRoles, SoftDeletes, Messageable,
        CascadeSoftDeletes, MediaConversionAble {
            MediaConversionAble::registerMediaConversions insteadof InteractsWithMedia;
        }


    public const RESET_PASSWORD_ABILITY = 'reset-password';

    /**
     * @var array|string[]
     */
    protected array $cascadeDeletes = ['social_accounts'];

    /**
     * @var array
     */
    protected $appends = ['permissions_ids'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'email',
        'password',
        'verified_at',
        'phone_view',
        'city_id',
        'latitude',
        'longitude',
        'country',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'verified_at' => 'datetime',
    ];

    /**
     * Add a mutator to ensure hashed passwords
     */
    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = bcrypt($password);
    }

    /**
     * @return array
     */
    public function getPermissionsIdsAttribute(): array {
        return $this->getAllPermissions()->pluck('id')->toArray();
    }

    /**
     * @return bool
     */
    public function passwordIsEmpty(): bool {
        return !$this->password;
    }

    /**
     * Add a mutator to ensure hashed passwords
     */
    public function getFullNameAttribute(): string {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * @return array
     */
    public function allBlockedArray(): array {
        $blockedListFromCurrentUser = $this->blockedListFromCurrentUser();
        $blockedListFromAnotherUser = $this->blockedListFromAnotherUser();
        return array_merge($blockedListFromCurrentUser, $blockedListFromAnotherUser);
    }

    /**
     * @return array
     */
    public function blockedListFromCurrentUser(): array {
        return \DB::table('blocked_users')
            ->where([
                ['user_id', '=', $this->id],
                ['blocked_id', '<>', $this->id],
            ])
            ->pluck('blocked_id')
            ->toArray();
    }

    /**
     * @return array
     */
    public function blockedListFromAnotherUser(): array {
        return \DB::table('blocked_users')
            ->where([
                ['user_id', '<>', $this->id],
                ['blocked_id', '=', $this->id],
            ])
            ->pluck('blocked_id')
            ->toArray();
    }

    /**
     * @return array
     */
    public function blockedChatFromAnotherUser(): array {
        return \DB::table('blocked_users')
          ->where([
            ['user_id', '<>', $this->id],
            ['blocked_id', '=', $this->id],
          ])
          ->get()
          ->toArray();
    }

    /**
     * @return array
     */
    public function getAdvertiseFavoritesIdsAttribute(): array {
        return \DB::table('advertise_statistics')
            ->select('advertise_id')
            ->where([
                'user_id' => $this->id,
                'type' => AdvertiseStatisticAlias::Favorite,
            ])
            ->pluck('advertise_id')
            ->toArray();
    }

    /**
     * @return string
     */
    public function getRegisteredAttribute(): string {
        $date = $this->created_at;

        if(!$date) return '';

        $now = $date->now();

        return $date->diffForHumans($now, true);
    }

    /**
     * @param $conversation_id
     * @return bool
     */
    public function existsConversation($conversation_id): bool {
        $chatPrefix = (new Conversation())->getPrefix();

        $conversation = $this->whereHas('conversations', function ($q) use ($conversation_id, $chatPrefix) {
            $q->where("{$chatPrefix}conversations.id", $conversation_id);
        })->first();

        return (bool)$conversation;
    }

    /**
     * @param array|string[] $abilities
     * @return string
     */
    public function createAccessToken(array $abilities = ['*']): string {
        return $this->createToken(
            config('app.name'),
            $abilities,
            Carbon::now()->addDays(10) // expiresAt
        )->plainTextToken;
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope('verified_at', function (Builder $builder) {
            $builder->whereNotNull('verified_at')->isNotBanned();
        });
    }

    /**
     * Scope a query to only include popular users.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsNotBanned($query)
    {
        return $query->where('banned', '=', 0);
    }

    /**
     * @return bool
     */
    public function isAdmin(): bool {
        return $this->hasRole('admin');
    }

    /**
     * @return bool
     */
    public function isUser(): bool {
        return $this->hasRole('user');
    }

    /**
     * @return bool
     */
    public function isModerator(): bool {
        return $this->hasRole('moderator');
    }

    /**
     * @return bool
     */
    public function hasActiveSubscription(): bool {
        return $this->subscription()
            ->where('status', '=', SubscriptionStatuses::ACTIVE)
            ->exists();
    }

    /**
     * @return BelongsToMany
     */
    public function favorites(): BelongsToMany {
        return $this->belongsToMany(Advertise::class,
            'advertise_statistics',
            'user_id',
            'advertise_id'
        );
    }

    /**
     * @return BelongsTo
     */
    public function city(): BelongsTo {
        return $this->belongsTo(City::class);
    }

    /**
     * @return BelongsToMany
     */
    public function advertise_favorites(): BelongsToMany {
        return $this->favorites()->wherePivot('type', AdvertiseStatisticAlias::Favorite);
    }

    /**
     * @return MorphMany
     */
    public function unread_notifications(): MorphMany {
        return $this->notifications()->whereNull('read_at');
    }

    /**
     * @return int
     */
    public function getIssetUnreadChatMessageAttribute(): int {
        $PART = ConfigurationManager::PARTICIPATION_TABLE;
        $CONV = ConfigurationManager::CONVERSATIONS_TABLE;
        $NOTI = ConfigurationManager::MESSAGE_NOTIFICATIONS_TABLE;
        $MESS = ConfigurationManager::MESSAGES_TABLE;
        $unread = \DB::table("$PART as PART")
            ->join("$CONV as CONV", function($join) {
                $join
                    ->on('PART.conversation_id', '=', 'CONV.id')
                    ->where('started', '<>', false);
            })
            ->leftJoin("advertises as ADVE",'CONV.advertise_id', '=', 'ADVE.id')
            ->where([
                'PART.messageable_id' => $this->getKey(),
                'PART.messageable_type' => $this->getMorphClass(),
            ])
            ->leftJoin("$NOTI as NOTI", function($join) use ($MESS) {
                $join
                    ->on('PART.messageable_id', '=', 'NOTI.messageable_id')
                    ->whereColumn('PART.messageable_type', 'NOTI.messageable_type')
                    ->whereColumn('CONV.id', 'NOTI.conversation_id')
                    ->where(['NOTI.is_seen' => false, 'NOTI.is_sender' => false])
                    ->where('CONV.started', '=', true)
                    ->join("$MESS as MESS", function($join) {
                        $join->on('NOTI.message_id', '=', 'MESS.id')->whereNull('MESS.deleted_at');
                    });
            })
            ->select(\DB::raw('COUNT(NOTI.id) as unread_count'))
            ->first();

        return $unread->unread_count;
    }

    /**
     * @return BelongsToMany
     */
    public function block_list(): BelongsToMany {
        return $this->belongsToMany(User::class,
            'blocked_users',
            'user_id',
            'blocked_id'
        );
    }

    /**
     * @return HasMany
     */
    public function advertises(): HasMany {
        return $this->hasMany(Advertise::class);
    }

    /**
     * @return HasMany
     */
    public function canceled_advertises(): HasMany {
        return $this->advertises()->where('status', '=', AdvertiseStatus::Rejected);
    }

    /**
     * @return HasManyThrough
     */
    public function conversations(): HasManyThrough {
        return $this->hasManyThrough(
            Conversation::class,
            Participation::class,
            'messageable_id',
            'id',
            'id',
            'conversation_id'
        );
    }

    /**
     * @param $blockedID
     * @return bool
     */
    public function isBlocked($blockedID): bool {
        return $this->block_list()->wherePivot('blocked_id','=', $blockedID)->exists();
    }

    /**
     * @return MorphOne
     */
    public function avatar(): MorphOne {
        return $this
            ->morphOne(config('media-library.media_model'), 'model')
            ->where('collection_name', '=', \App\Enums\MediaCollections::USER_AVATAR_COLLECTION)
            ->orWhere('collection_name', '=', \App\Enums\MediaCollections::MODERATOR_USER_AVATAR_COLLECTION);
    }

    /**
     * @return HasMany
     */
    public function social_accounts(): HasMany {
        return $this->hasMany(SocialAccount::class);
    }

    /**
     * @return HasMany
     */
    public function subscription(): HasMany {
        return $this->hasMany(Subscription::class);
    }

    /**
     * @return HasMany
     */
    public function tickets_user(): HasMany {
        return $this->hasMany(Ticket::class, 'user_id');
    }

    /**
     * @return HasMany
     */
    public function moderator_tickets(): HasMany {
        return $this->hasMany(Ticket::class, 'moderator_id');
    }

    /**
     * Scope a query to only include users of a given type.
     *
     * @param $query
     * @param string $permission
     * @return mixed
     */
    public function scopeModeratorCan($query, string $permission): mixed {
        // TODO менять потом срочно
        $perm = \DB::table('permissions')->where('name','=', $permission)->first();

        return $query->whereHas('roles', function ($query) use ($perm) {
            $query->whereName('moderator')
                ->join('model_has_permissions as mp', 'mp.model_id', '=', 'users.id')
                ->where('permission_id', '=', $perm->id);
        });
    }
}
