<?php

namespace App\Http\Requests\V1\Users;

use App\Http\Requests\V1\FormRequest;

/**
 * Class UpdatePictureAnCityRequest
 * @package App\Http\Requests\V1\Users
 */
class UpdatePictureAnCityRequest extends FormRequest {

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
            'file' => 'mimes:jpeg,jpg,png,svg,gif,webp|required|max:2048',
            'city_id' => 'required|exists:cities,id',
        ];
    }
}
