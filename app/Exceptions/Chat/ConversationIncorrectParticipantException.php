<?php

namespace App\Exceptions\Chat;

use Exception;
use Illuminate\Database\Eloquent\Model;
use JetBrains\PhpStorm\Pure;

/**
 * Class CascadeSoftRestoreException
 * @package App\Exceptions
 */
class ConversationIncorrectParticipantException extends Exception
{
    /**
     * @param Model $invalidParticipant
     * @return static
     */
    #[Pure] public static function invalidParticipant(Model $invalidParticipant): static {
        return new static(sprintf(
            '%s does not belong to this Conversation',
            get_class($invalidParticipant)
        ));
    }
}
