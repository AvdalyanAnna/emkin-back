<?php

namespace App\Observers;

use App\Models\SocialAccount;
use App\Models\User;

/**
 * Class UserObserver
 * @package App\Observers
 */
class UserObserver {


    /**
     * Handle the User "created" event.
     *
     * @param User $user
     * @return void
     */
    public function created(User $user) { }

    /**
     * Handle the User "updated" event.
     *
     * @param User $user
     * @return void
     */
    public function updated(User $user) {
        $this->deleteProviderEmailIfChangedUserEmail($user);
    }

    /**
     * Handle the User "deleted" event.
     *
     * @param User $user
     * @return void
     */
    public function deleted(User $user) { }

    /**
     * Handle the User "restored" event.
     *
     * @param User $user
     * @return void
     */
    public function restored(User $user) { }

    /**
     * Handle the User "force deleted" event.
     *
     * @param User $user
     * @return void
     */
    public function forceDeleted(User $user) { }

    /**
     * Проверяет если у пользователя нет пороля при регистраци или
     *
     * @param User $user
     * @return void
     */
    protected function deleteProviderEmailIfChangedUserEmail(User $user): void {
        if ($user->isDirty('email')) {
            SocialAccount::where([
                'user_id' => $user->id,
                'provider_email' => $user->getOriginal('email'),
            ])->delete();
        }
    }
}
