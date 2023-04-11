<?php

namespace App\Http\Requests\V1\Users;

use App\Http\Requests\V1\FormRequest;

/**
 * Class ConfirmationCodeRequest
 * @package App\Http\Requests\V1\Users
 */
class PaymentRequest extends FormRequest {

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        return !$this->user()->hasActiveSubscription();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array {
       return [
           'id' => 'required|exists:commercial_users',
           'success_url' =>  'required|string',
           'cancel_url' =>  'required|string',
       ];
    }
}
