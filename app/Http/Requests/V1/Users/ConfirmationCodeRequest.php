<?php

namespace App\Http\Requests\V1\Users;

use App\Http\Requests\V1\FormRequest;
use App\Models\Notification;
use App\Traits\FormRequestAuthorizeAble;

/**
 * Class ConfirmationCodeRequest
 * @package App\Http\Requests\V1\Users
 */
class ConfirmationCodeRequest extends FormRequest {

    use FormRequestAuthorizeAble;

    /**
     * @var array|string[]
     */
    protected array $rulesEmail = [
        Notification::Email_CONFIRMATION => [
            'email' => 'required|string|email',
        ],
        Notification::SMS_CONFIRMATION => [
            'phone' => 'required|max:15|regex:/^[0-9]+$/',
        ],
    ];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array {
        return array_merge([
            'code' => 'required|min:6|max:6|regex:/^[0-9]+$/'
        ], $this->rulesEmail[$this->get('confirmation_type')]);
    }
}
