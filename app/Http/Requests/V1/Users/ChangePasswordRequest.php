<?php

namespace App\Http\Requests\V1\Users;

use App\Http\Requests\V1\FormRequest;
use App\Rules\MatchOldPassword;
use Illuminate\Validation\Rules\Password;

/**
 * Class ChangePasswordRequest
 * @package App\Http\Requests\V1\Users
 */
class ChangePasswordRequest extends FormRequest {

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        if ($this->has('field')) {
            $this->request->remove($this->get('field'));
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array {
        $passwordRule = Password::min(8)
            ->letters()
            ->mixedCase()
            ->numbers()
            ->symbols()
            ->uncompromised(3);

        return [
            'current_password' => ['required', new MatchOldPassword],
            'password' => ['nullable','different:current_password','required_with:current_password', 'confirmed', $passwordRule],
            "password_confirmation" =>"nullable|required_with:password|required_with:current_password",
            "confirmation_auth" =>"required_with:current_password",
            'code' => 'required|min:6|max:6|regex:/^[0-9]+$/'
        ];
    }
}
