<?php declare(strict_types=1);

namespace App\Enums\Advertise;

use BenSampo\Enum\Enum;

/**
 * @method static static VIP()
 * @method static static REGULAR()
 */
final class AdvertiseType extends Enum
{
    /**
     * Обычные Регулярные объявления
     */
    const REGULAR = 0;

    /**
     * ВИП Проплаченные объявления, имеют приоритет при выводе в списке
     */
    const VIP = 1;
}
