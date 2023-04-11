<?php declare(strict_types=1);

namespace App\Enums\Chat;

use BenSampo\Enum\Enum;

/**
 * Class ChatServiceNamesEnum
 * @package App\Enums\Chat
 */
final class ChatServiceNamesEnum extends Enum
{
    /**
     * Chat Conversation Service
     */
    const CONVERSATIONS_SERVICE = 'conversationService';
    /**
     * Chat Message Service
     */
    const MESSAGES_SERVICE = 'messageService';
}
