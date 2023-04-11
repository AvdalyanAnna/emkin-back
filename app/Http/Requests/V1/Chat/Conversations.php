<?php

namespace App\Http\Requests\V1\Chat;

use App\Http\Requests\V1\FormRequest;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Class Conversations
 * @package App\Http\Requests\V1\Chat
 */
class Conversations extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        return true;
    }

    /**
     * @return string[]
     */
    #[ArrayShape(['conversations' => "string", 'conversations.*' => "string"])] public function rules(): array {
        return [
            'conversations' => 'required|array',
            'conversations.*' => 'required|numeric|exists:chat_conversations,id',
        ];
    }
}
