<?php declare(strict_types=1);

namespace App\Enums\Users;

use BenSampo\Enum\Enum;

/**
 * @method static static PASSIVE()
 * @method static static ACTIVE()
 */
final class SubscriptionStatuses extends Enum
{
    /**
     * Пассивная Подписка
     */
    const PASSIVE = 0;

    /**
     * Активная Подписка
     */
    const ACTIVE = 1;
}
