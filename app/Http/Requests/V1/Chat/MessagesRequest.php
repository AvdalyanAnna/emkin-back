<?php

namespace App\Http\Requests\V1\Chat;

use App\Http\Requests\V1\FormRequest;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Class MessagesRequest
 * @package App\Http\Requests\V1\Chat
 */
class MessagesRequest extends FormRequest
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
    #[ArrayShape(['messages' => "string", 'messages.*' => "string"])] public function rules(): array {
        return [
            'messages' => 'required|array',
            'messages.*' => 'required|numeric|exists:chat_messages,id',
        ];
    }
}
