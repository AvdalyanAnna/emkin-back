<?php declare(strict_types=1);

namespace App\Enums\Advertise;

use BenSampo\Enum\Enum;

/**
 * @method static static NotVerified()
 * @method static static Active()
 * @method static static InActive()
 * @method static static Rejected()
 * @method static static Banned()
 * @method static static Draft()
 */
final class AdvertiseStatus extends Enum
{

    /**
     * Самый базовый статус, присваивается при создании карточки, присутствует в листинге.
     */
    const NotVerified = 0;

    /**
     * Обычное объявление, фигурирующее в листинге
     */
    const Active = 1;

    /**
     * Объявление которое перестает быть активным. Например, спустя 30 дней после публикации,
     * ему автоматически присваивается этот статус; или при переносе в черновик.
     */
    const InActive = 2;

    /**
     * Модератор проверил объявление и отклонил его
     */
    const Rejected = 3;

    /**
     * Объявление нарушающее правила сервиса, без возможности восстановления
     */
    const Banned = 4;

    /**
     * Черновик
     */
    const Draft = 5;
}
