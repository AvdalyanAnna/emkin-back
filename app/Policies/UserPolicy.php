<?php

namespace App\Policies;

use App\Models\User;
use App\Repositories\V1\Users\NotificationRepository;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

/**
 * Class UserPolicy
 * @package App\Policies
 */
class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param User $model
     * @return Response|bool
     */
    public function update(User $user, User $model): Response|bool {
        return $model->id === $user->id;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param User $model
     * @param string $code
     * @return Response|bool
     */
    public function changePassword(User $user, User $model, string $code): Response|bool {

        if ($model->id !== $user->id) return false;

        $correct = app(NotificationRepository::class)->checkConfirmationCode($user, $code);

        return $correct
            ? Response::allow()
            : Response::deny('Your Code is incorrect');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param User $model
     * @return Response|bool
     */
    public function exportData(User $user, User $model): Response|bool {
        return $user->isAdmin() || ($user->isModerator() && $user->can('access_excel_data_users'));
    }
}
