<?php

namespace App\Http\Requests\V1\Chat;

use Illuminate\Foundation\Http\FormRequest;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Class UpdateConversation
 * @package App\Http\Requests\V1\Chat
 */
class UpdateConversation extends FormRequest
{

    /**
     * @return bool
     */
    public function authorized(): bool {
        return true;
    }

    /**
     * @return string[]
     */
    #[ArrayShape(['data' => "string"])] public function rules(): array {
        return [
            'data' => 'array',
        ];
    }
}
