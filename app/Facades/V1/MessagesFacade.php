<?php

namespace App\Facades\V1;

use Illuminate\Support\Facades\Facade;

/**
 * Class MessagesFacade
 * @package App\Facades\V1
 */
class MessagesFacade extends Facade {

    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string { return 'messages'; }
}
