<?php

namespace App\Http\Requests\V1\Chat;

use App\Http\Requests\V1\FormRequest;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Class StoreMessage
 * @package App\Http\Requests\V1\Chat
 */
class StoreMessage extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool {
        return true;
    }

    /**
     * @return string[]
     */
    #[ArrayShape(['message' => "string", 'files' => "string", 'files.*' => "string"])] public function rules(): array {
        return [
            'message' => 'required_without:files|string|max:300|min:1',
            'files' => 'required_without:message|array|max:5',
            'files.*' => 'required|mimes:jpeg,jpg,png,svg,gif,webp|max:2048',
        ];
    }
}
