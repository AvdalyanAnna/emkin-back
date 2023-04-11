<?php

namespace App\Http\Requests\V1\Admin\Permissions;

use App\Http\Requests\V1\FormRequest;

/**
 * Class GivePermissionRequest
 * @package App\Http\Requests\V1\Admin\Permissions
 */
class GivePermissionRequest extends FormRequest {

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
    public function rules (): array {
        return [
           'permissions' => 'required|array',
           'user_id' => 'required|exists:users,id'
        ];
    }
}
