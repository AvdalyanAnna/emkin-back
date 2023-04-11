<?php

namespace App\Http\Requests\V1\Users;

use App\Http\Requests\V1\FormRequest;
use Illuminate\Validation\Rules;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Class ResetPasswordRequest
 * @package App\Http\Requests\V1\Users
 */
class ResetPasswordRequest extends FormRequest {

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

        $passwordRule = Rules\Password::min(8)
            ->letters()
            ->mixedCase()
            ->numbers()
            ->symbols()
            ->uncompromised(3);

        return [
            'password' => ['required', 'confirmed', $passwordRule],
        ];
    }

    /**
     * @return array
     */
    #[ArrayShape(['password' => "mixed"])] public function attributes(): array {
        return [
            'password' => __('validation.password.attributes.password'),
        ];
    }
}
