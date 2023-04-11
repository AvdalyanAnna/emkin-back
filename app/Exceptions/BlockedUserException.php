<?php

namespace App\Exceptions;

/**
 * Class BlockedAnotherUserException
 * @package App\Exceptions
 */
class BlockedUserException extends \Exception
{
    /**
     * @param bool $blockerIsAuthUser
     * @return static
     */
    static function blockedUser(bool $blockerIsAuthUser = true): static {

        if (!$blockerIsAuthUser) {
            return new static(__('messages.ANOTHER_BLOCKED'));
        }

        return new static(__('messages.CURRENT_BLOCKED'));
    }
}
