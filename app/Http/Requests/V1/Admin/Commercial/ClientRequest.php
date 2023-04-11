<?php

namespace App\Http\Requests\V1\Admin\Commercial;

use App\Http\Requests\V1\FormRequest;

/**
 * Class ClientRequest
 * @package App\Http\Requests\V1\Admin\Commercial
 */
class ClientRequest extends FormRequest {

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
            'id' => 'nullable|exists:clients',
            'file' => 'nullable|mimes:jpeg,jpg,png,svg,gif,webp|max:2048',
            'first_name' => 'nullable|string|max:50',
            'last_name' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:100',
            'email' => 'nullable|string|email|unique:clients,email',
            'phone' => 'nullable|string|max:15|regex:/^[0-9]+$/|unique:clients,phone',
            'phone_view' => 'nullable|string',
        ];
    }
}
