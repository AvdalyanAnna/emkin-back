<?php

namespace App\Http\Requests\V1\Auth;

use App\Http\Requests\V1\FormRequest;
use App\Models\Notification;
use App\Traits\FormRequestAuthorizeAble;
use Illuminate\Validation\Rules\Password;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Class AuthRequest
 * @package App\Http\Requests\V1\Users
 */
class AuthRequest extends FormRequest {

    use FormRequestAuthorizeAble;

    /**
     * @var array|string[]
     */
    protected array $rulesEmail = [
        Notification::Email_CONFIRMATION => [
            'email' => 'required|string|email|unique:users,email',
        ],
        Notification::SMS_CONFIRMATION => [
            'phone' => 'required|string|max:15|regex:/^[0-9]+$/|unique:users,phone',
        ],
    ];

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

        $rule = [
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'policy' => 'required|boolean',
            'password' => ['required', 'string', $passwordRule],
        ];

        $emailOrPhoneRule = $this->rulesEmail[$this->get('confirmation_type')];

        return array_merge($rule, $emailOrPhoneRule);
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
