<?php declare(strict_types=1);

namespace App\Enums\Page;

use BenSampo\Enum\Enum;

/**
 * Class PageTypes
 * @package App\Enums\Advertise
 *
 * @method static static FrontDesign
 * @method static static BackDesign
 * @method static static Menu
 * @method static static Contact
 * @method static static Popup
 */
final class PageTypes extends Enum
{
    /**
     * Тип Для страничек фронта
     */
    const FrontDesign = 'front_design';

    /**
     * Тип Для страничек личных кабинет пользователя
     */
    const BackDesign = 'back_design';

    /**
     * Тип Для меню сайта
     */
    const Menu = 'menu';

    /**
     * Тип Для Контактов сайта
     */
    const Contact = 'contact';

    /**
     * Тип Для Модалов сайта
     */
    const Popup = 'popup';
}
