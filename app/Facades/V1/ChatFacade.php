<?php

namespace App\Facades\V1;

use Illuminate\Support\Facades\Facade;

/**
 * Class ChatFacade
 * @package App\V1\Facades
 */
class ChatFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     * @codeCoverageIgnore
     */
    protected static function getFacadeAccessor(): string {
        self::clearResolvedInstance(Chat::class);

        return Chat::class;
    }
}
