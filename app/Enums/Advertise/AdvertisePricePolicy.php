<?php declare(strict_types=1);

namespace App\Enums\Advertise;

use BenSampo\Enum\Enum;

/**
 * @method static static FREE()
 * @method static static PAID()
 * @method static static EXCHANGE()
 */
final class AdvertisePricePolicy extends Enum
{
    /**
     * ценовая политика - бесплатное
     */
    const FREE = 0;

    /**
     * ценовая политика - платное
     */
    const PAID = 1;

    /**
     * ценовая политика - обмен
     */
    const EXCHANGE = 2;
}
