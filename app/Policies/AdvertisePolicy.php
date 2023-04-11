<?php

namespace App\Policies;

use App\Models\Advertise;
use App\Models\Media;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Auth;

/**
 * Class AdvertisePolicy
 * @package App\Policies
 */
class AdvertisePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can create models.
     *
     * @return bool
     */
    public function store(): bool {
        return Auth::check();
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Advertise $advertise
     * @return bool
     */
    public function update(User $user, Advertise $advertise): bool {
       return $user->id === $advertise->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Advertise $advertise
     * @return bool
     */
    public function changeProductStatusOrDeleteProduct(User $user, Advertise $advertise): bool {
        return $user->id === $advertise->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Advertise $advertise
     * @param Media $media
     * @return bool
     */
    public function deletePicture(User $user, Advertise $advertise, Media $media): bool {
        return $user->id === $advertise->user_id && $media->model_id = $advertise->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Advertise $advertise
     * @return bool
     */
    public function changeStatus(User $user, Advertise $advertise): bool {
        return $user->hasRole(config('roles.roles.admin.name'))
            || $user->hasRole(config('roles.roles.moderator.name'));
    }
}
