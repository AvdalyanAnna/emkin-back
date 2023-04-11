<?php

namespace App\Http\Requests\V1\Admin\Users;

use App\Http\Requests\V1\FormRequest;

/**
 * Class UpdateProfileRequest
 * @package App\Http\Requests\V1\Users
 */
class ModeratorUpdateRequest extends FormRequest {

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        return true;
    }

    public array $rules = [
        'POST' => [
            'file' => 'nullable|mimes:jpeg,jpg,png,svg,gif,webp|max:2048',
            'first_name' => 'nullable|string|max:100|min:2',
            'last_name' => 'nullable|string|max:100|min:2',
            'email' => 'nullable|string|email|unique:users,email',
            'phone' => 'nullable|string|max:15|regex:/^[0-9]+$/|unique:users,phone',
            'password' => 'nullable|string|confirmed|min:8',
        ],
        'PUT' => [
            'id' => 'required|exists:users',
            'file' => 'nullable|mimes:jpeg,jpg,png,svg,gif,webp|max:2048',
            'first_name' => 'nullable|string|max:100|min:2',
            'last_name' => 'nullable|string|max:100|min:2',
            'email' => 'nullable|string|email|unique:users,email',
            'phone' => 'nullable|string|max:15|regex:/^[0-9]+$/|unique:users,phone',
            'password' => 'nullable|string|confirmed|min:8',
        ],
    ];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array {
        return $this->rules[$this->getMethod()];
    }
}
