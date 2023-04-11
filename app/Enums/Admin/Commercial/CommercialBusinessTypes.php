<?php declare(strict_types=1);

namespace App\Enums\Admin\Commercial;

use BenSampo\Enum\Enum;

/**
 * @method static static VERTICAL()
 * @method static static HORIZONTAL()
 */
final class CommercialBusinessTypes extends Enum
{
    /**
     * указываем только превьюшку и опциями правим внешний вид (Banner Constructor);
     */
    const VERTICAL = 0;

    /**
     * указываем полностью изображение баннера (Custom).
     */
    const HORIZONTAL = 1;
}
