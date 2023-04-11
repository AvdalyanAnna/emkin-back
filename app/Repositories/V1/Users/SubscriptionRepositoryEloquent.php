<?php

namespace App\Repositories\V1\Users;

use App\Repositories\V1\Base\BaseRepository;

/**
 * Class SubscriptionRepositoryEloquent
 * @package App\Repositories\V1\Users
 */
class SubscriptionRepositoryEloquent extends BaseRepository implements SubscriptionRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model(): string {
        return \App\Models\Subscription::class;
    }
}
