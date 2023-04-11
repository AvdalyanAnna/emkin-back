<?php

namespace App\Http\Requests\V1\Admin\Commercial;

use App\Http\Requests\V1\FormRequest;

/**
 * Class CommercialUsersRequest
 * @package App\Http\Requests\V1\Users
 */
class CommercialUsersRequest extends FormRequest {

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
            'id' => 'nullable|exists:commercial_users',
            'files' => 'nullable|array|max:10',
            'files.*.media_id' => 'required|exists:media,id',
            'name' => 'required|string|max:100|min:2',
            'description' => 'required|string|max:500|min:10',
            'price' => 'required|regex:/^[1-9][0-9]+/|not_in:0',
            'status' => 'required|numeric|between:0,2',
            'period_days' => 'required|numeric',
            'gep_up' => 'required|numeric|not_in:0',
            'period_of_stay_id' => 'required|exists:period_of_stays,id',
        ];
    }
}
