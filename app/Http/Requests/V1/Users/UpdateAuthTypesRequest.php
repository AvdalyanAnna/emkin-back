<?php

namespace App\Http\Requests\V1\Users;

use App\Http\Requests\V1\FormRequest;
use App\Models\Notification;
use Illuminate\Validation\Rule;

/**
 * Class UpdateAuthTypesRequest
 * @package App\Http\Requests\V1\Users
 */
class UpdateAuthTypesRequest extends FormRequest {

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
        $rulesEmail = [
            Notification::Email_CONFIRMATION => [
                'email' => 'required|string|email|' . Rule::unique('users')->ignore(user()->id),
            ],
            Notification::SMS_CONFIRMATION => [
                'phone' => 'required|string|max:15|regex:/^[0-9]+$/|' . Rule::unique('users')->ignore(user()->id),
            ],
        ];

        return $rulesEmail[$this->get('confirmation_type')];
    }
}
