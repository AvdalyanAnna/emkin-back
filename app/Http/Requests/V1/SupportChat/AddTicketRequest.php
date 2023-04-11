<?php

namespace App\Http\Requests\V1\SupportChat;

use App\Http\Requests\V1\FormRequest;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Class AddTicketRequest
 * @package App\Http\Requests\V1\Users
 */
class AddTicketRequest extends FormRequest {

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool { return true; }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    #[ArrayShape([
        'files' => "array",
        'files.*' => "file",
        'name' => "string",
        'email' => "string",
        'support_theme_id' => "string",
        'description' => "string"
    ])] public function rules(): array {
        return [
            'files' => 'nullable|array|max:5',
            'files.*' => 'required|file|max:2560', // 2.5 mb
            'name' => 'nullable|string|max:50',
            'email' => 'nullable|email|string|max:100',
            'support_theme_id' => 'nullable|exists:support_themes,id',
            'description' => 'required|string|max:1000',
        ];
    }
}
