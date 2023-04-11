<?php

namespace App\Repositories\V1\Users;

use App\Models\User;
use App\Repositories\V1\Base\BaseContract;

/**
 * Interface UserRepository.
 *
 * @package namespace App\Repositories\Users;
 * @method getModel()
 * @method moderatorCan(mixed $config)
 */
interface UserRepository extends BaseContract
{

    /**
     * @param array $attributes
     * @param string $collection_name
     * @return User
     */
    public function addUser(array $attributes, string $collection_name): User;
}
