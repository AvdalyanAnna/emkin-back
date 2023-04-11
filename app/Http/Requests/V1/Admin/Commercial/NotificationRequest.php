<?php

namespace App\Http\Requests\V1\Admin\Commercial;

use App\Http\Requests\V1\FormRequest;

/**
 * Class NotificationRequest
 * @package App\Http\Requests\V1\Users
 */
class NotificationRequest extends FormRequest {

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array {
        return [
            'id' => 'nullable|exists:commercial_notifications',
            'file' => 'nullable|mimes:jpeg,jpg,png,svg,gif,webp|max:2048',
            'title' => 'required|string|max:100|min:2',
            'description' => 'required|string|max:3000|min:10',
            'link' => 'nullable|string|max:100|min:2',
            'details' => 'nullable|string',
            'status' => 'required|boolean',
            'text' => 'required|boolean',
        ];
    }
}
