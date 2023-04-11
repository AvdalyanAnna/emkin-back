<?php declare(strict_types=1);

namespace App\Enums\Admin\Moderator;

use BenSampo\Enum\Enum;

/**
 * Class ModeratorStatisticsEnum
 * @package App\Enums\Admin\Moderator
 */
final class ModeratorStatisticsEnum extends Enum
{
    /**
     * Кол-во просмотренных объявлений (view details count)
     */
    const VIEWED_ADS = 1;

    /**
     * Общее кол-во одобренных объявлений
     */
    const APPROVED_ADS = 2;

    /**
     * Общее кол-во отказанных объявлений.
     */
    const REJECTED_ADS = 3;

    /**
     * Кол-во забанненых пользователей
     */
    const BANNED_USERS = 4;

    /**
     * Кол-во разбаненных пользователей
     */
    const UNBANNED_USERS = 5;

    /**
     * Кол-во завершенных запросов в поддержке
     */
    const CLOSED_TICKETS = 6;

    /**
     * Кол-во не завершенных запросов в поддержке.
     */
    const PENDING_TICKETS = 7;

    /**
     * Кол-во передевенных запросов поддержки на дургого менеджера
     */
    const REQUEST_TO_ANOTHER_MANAGER = 8;
}
