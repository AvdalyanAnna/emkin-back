<?php

namespace App\Chat;

use JetBrains\PhpStorm\ArrayShape;

/**
 * Class ConfigurationManager
 * @package App\Chat
 */
class ConfigurationManager
{
    const CONVERSATIONS_TABLE = 'chat_conversations';
    const MESSAGES_TABLE = 'chat_messages';
    const MESSAGE_NOTIFICATIONS_TABLE = 'chat_message_notifications';
    const PARTICIPATION_TABLE = 'chat_participation';

    /**
     * @return array
     */
    #[ArrayShape([
        'page' => "int|mixed",
        'perPage' => "int|mixed",
        'sorting' => "mixed|string",
        'columns' => "mixed|string[]",
        'pageName' => "mixed|string",
        'user' => "mixed|null"
    ])] public static function paginationDefaultParameters(): array {
        $pagination = config('chat.pagination', []);

        return [
            'page'     => $pagination['page'] ?? 1,
            'perPage'  => $pagination['perPage'] ?? 25,
            'sorting'  => $pagination['sorting'] ?? 'asc',
            'columns'  => $pagination['columns'] ?? ['*'],
            'pageName' => $pagination['pageName'] ?? 'page',
            'user' => $pagination['user'] ?? null,
        ];
    }
}
