<?php declare(strict_types=1);

namespace App\Enums\Advertise;

use BenSampo\Enum\Enum;

/**
 *
 *
 * Class AdvertiseContacts
 * @package App\Enums\Advertise
 *
 * @method static static ALL()
 * @method static static PHONE()
 * @method static static MESSAGE()
 */
final class AdvertiseContacts extends Enum
{
    /**
     * Телефон И Сообщение
     */
    const ALL = 0;

    /**
     * Телефон
     */
    const PHONE = 1;

    /**
     * Сообщение
     */
    const MESSAGE = 2;
}
