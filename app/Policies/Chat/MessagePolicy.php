<?php

namespace App\Policies\Chat;

use App\Models\Advertise;
use App\Models\Message;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Auth;

/**
 * Class MessagePolicy
 * @package App\Policies\Chat
 */
class MessagePolicy
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
     * @param User $user
     * @param Message $message
     * @return bool
     */
    public function destroy(User $user, Message $message): bool {
        return $user->id === $message->participation_id;
    }
}
