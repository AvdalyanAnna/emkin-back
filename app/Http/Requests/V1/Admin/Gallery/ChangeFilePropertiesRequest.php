<?php

namespace App\Http\Requests\V1\Admin\Gallery;

use App\Http\Requests\V1\FormRequest;

/**
 * Class PageRequest
 * @package App\Http\Requests\V1
 */
class ChangeFilePropertiesRequest extends FormRequest {

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
           'alt' => 'nullable|string',
           'header' => 'nullable|string',
           'description' => 'nullable|string|max:2500',
           'file' => 'nullable|file|mimes:jpeg,jpg,png|max:2560', // 2.5 mb
       ];
    }
}
