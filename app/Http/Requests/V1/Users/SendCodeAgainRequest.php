<?php

namespace App\Http\Requests\V1\Users;

use App\Http\Requests\V1\FormRequest;
use App\Models\Notification;
use App\Traits\FormRequestAuthorizeAble;

/**
 * Class SendCodeAgainRequest
 * @package App\Http\Requests\V1\Users
 */
class SendCodeAgainRequest extends FormRequest {

    use FormRequestAuthorizeAble;

    /**
     * @var array|string[]
     */
    protected array $rulesEmail = [
        Notification::Email_CONFIRMATION => [
            'email' => 'required|string|email|exists:users,email',
        ],
        Notification::SMS_CONFIRMATION => [
            'phone' => 'required|max:15|regex:/^[0-9]+$/|exists:users,phone',
        ],
    ];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array {
        return $this->rulesEmail[$this->get('confirmation_type')];
    }
}
