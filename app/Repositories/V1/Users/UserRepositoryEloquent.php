<?php

namespace App\Repositories\V1\Users;

use App\Criteria\V1\SearchCriteria;
use App\Criteria\V1\Users\VerifiedCriteria;
use App\Enums\MediaCollections;
use App\Models\User;
use App\Repositories\V1\Base\BaseRepository;
use App\Traits\UploadAble;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Exceptions\RepositoryException;
use Throwable;

/**
 * Class UserRepositoryEloquent.
 *
 * @package namespace App\Repositories\Users;
 */
class UserRepositoryEloquent extends BaseRepository implements UserRepository
{
    use UploadAble;

    /**
     * @var array
     */
    protected $fieldSearchable = [
        'id',
        'first_name' => 'LIKE',
        'last_name' => 'LIKE',
        'created_at' => 'between',
        'phone',
        'email',
        'banned'
    ];

    /**
     * Boot up the repository, pushing criteria
     * @throws RepositoryException
     */
    public function boot() {
        $this->pushCriteria(app(SearchCriteria::class));
    }

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model(): string {
        return \App\Models\User::class;
    }

    /**
     * @param $user
     * @param array $data
     * @param string $collection_name
     * @param bool $enableUpdateHiddenFields
     * @throws Throwable
     * @return User
     */
    public function updateUserProfile(
        $user,
        array $data,
        string $collection_name = MediaCollections::USER_AVATAR_COLLECTION,
        bool $enableUpdateHiddenFields = false
    ): User {
        $user_id = $user->id;
        DB::transaction(function () use($user, $data, $enableUpdateHiddenFields, $collection_name) {

            if (existsUploadAbleFileInArray($data)) {
                $user->clearMediaCollection($collection_name);
                $this->addResponsiveImage($user, $data['file'], $collection_name);
            }

            $this->fillFields($data, $user, $enableUpdateHiddenFields);

            $user->update();
        });

        return $this->with(['avatar', 'city.country'])->find($user_id);
    }

    /**
     * @param array $attributes
     * @param string $collection_name
     * @throws Throwable
     * @return User
     */
    public function addUser(array $attributes, string $collection_name = MediaCollections::USER_AVATAR_COLLECTION): User {

        DB::transaction(function () use($attributes, &$user, $collection_name) {
            $user = $this->create($attributes);

            if (existsUploadAbleFileInArray($attributes)) {
                $this->addResponsiveImage($user, $attributes['file'], $collection_name);
            }
        });

        return $user;
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function givePermissionUser(array $data): mixed {
        $user = $this->findByField(['id' => $data['user_id']])->first();
        return $user->syncPermissions($data['permissions']);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function giveRoleUser(array $data): mixed {
        $user = $this->findByField(['id' => $data['user_id']])->first();
        return $user->syncRoles([$data['role_id']]);
    }

    /**
     * @param array $data
     * @param bool $applyVerifyCriteria
     * @throws RepositoryException
     * @return mixed
     */
    public function getUserByEmailOrPhone(array $data, bool $applyVerifyCriteria = true): mixed {
        $field = $data['field'];

        if ($applyVerifyCriteria) {
            $this->pushCriteria(VerifiedCriteria::class);
        }

        return $this->withoutGlobalScopes('verified_at', [
            $field => $data[$field]
        ])->firstOrFail();
    }

    /**
     * @param $userID
     * @param int|null $status
     * @return mixed
     */
    public function getStatistic($userID, ?int $status = null): mixed {
        return DB::table('advertises')
            ->when($status !== null, function ($q) use ($status) {
                $q->where('status', '=', $status);
            })
            ->select([
                DB::raw("SUM(show_phone) as show_phone_count"),
                DB::raw("SUM(show_details) as show_details_count"),
                DB::raw("SUM(added_favorites) as added_favorites_count"),
            ])
            ->where('user_id', $userID)
            ->first();
    }

    /**
     * @param array $attributes
     * @param $user
     * @param bool $enableUpdateHiddenFields
     */
    public function fillFields(array $attributes, &$user, bool $enableUpdateHiddenFields) : void {

        $disableUpdatesFields = ['password', 'verified_at', 'email', 'phone'];

        $fields = $user->getFillable();

        foreach ($fields as $field) {

            if (isset($attributes[$field])) {

                if (in_array($field, $disableUpdatesFields) && !$enableUpdateHiddenFields) continue;

                $user->fill([$field => $attributes[$field]]);
            }
        }
    }
}
