<?php declare(strict_types=1);

namespace App\Enums\Advertise;

use BenSampo\Enum\Enum;

/**
 * Class AdvertiseStatistic
 * @package App\Enums\Advertise
 *
 * @method static static PhoneView()
 * @method static static ShowDetails()
 * @method static static Favorite()
 */
final class AdvertiseStatistic extends Enum
{
    /**
     * Счетчик кнопки с телефоном
     */
    const PhoneView = 0;

    /**
     * Счетчик просмотра странички
     */
    const ShowDetails = 1;

    /**
     * Счетчик добавления в избранное
     */
    const Favorite = 2;
}
