<?php declare(strict_types=1);

namespace App\Enums\Admin\Commercial;

use BenSampo\Enum\Enum;

/**
 * @method static static CONSTRUCTOR()
 * @method static static CUSTOM()
 */
final class CommercialStatuses extends Enum
{
    /**
     * Draft - публикации которые в черновике, так же не активные
     */
    const DRAFT = 0;

    /**
     * Active - активные публикации которые действуют прямо сейчас
     */
    const ACTIVE = 1;

    /**
     * Closed - публикации которые отключены, и не активны
     */
    const CLOSED = 2;
}
