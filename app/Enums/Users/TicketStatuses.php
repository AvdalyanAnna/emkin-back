<?php declare(strict_types=1);

namespace App\Enums\Users;

use BenSampo\Enum\Enum;

/**
 * Статусы тикетов
 *
 * @method static static CLOSE()
 * @method static static NEW()
 * @method static static VIEWED()
 * @method static static ACCEPTED()
 * @method static static EXPECTATION()
 */
final class TicketStatuses extends Enum
{
    /**
     * Закрытый
     */
    const CLOSE  = 0;

    /**
     * Создан, и его еще никто не увидел, не ответил
     */
    const NEW = 1;

    /**
     * Модератор принял билет (Пользователь ждет комментарий поддержки)
     */
    const EXPECTATION = 2;

    /**
     * Пользователь посмотрел ответ поддержки, но ничего не написал. Если пользователь ничего не пишет на протяжении
     * 2 суток 48 часов, то тикет автоматически меняет статус на “закрытый” 0
     */
    const VIEWED = 3;
}
