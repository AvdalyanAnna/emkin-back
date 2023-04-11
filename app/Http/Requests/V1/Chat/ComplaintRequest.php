<?php

namespace App\Http\Requests\V1\Chat;

use App\Http\Requests\V1\FormRequest;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Class ComplaintRequest
 * @package App\Http\Requests\V1\Chat
 */
class ComplaintRequest extends FormRequest
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
    public function rules(): array {
        return [
            'files' => 'nullable|array|max:3',
            'files.*' => 'required|file|max:2560', // 2.5 mb
            'refusal_id' => 'nullable|exists:refusals,id',
            'description' => 'required|string|max:1000',
        ];
    }
}
