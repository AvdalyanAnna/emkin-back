<?php

namespace App\Exceptions\Chat;

use Exception;
use JetBrains\PhpStorm\Pure;

/**
 * Class IncorrectServiceInstanceException
 * @package App\Exceptions\Chat
 */
class IncorrectServiceInstanceException extends Exception
{
    /**
     * @param string $service
     * @return static
     */
    #[Pure] public static function invalidService(string $service): static {
        return new static(sprintf('%s Service is invalid', $service));
    }
}
