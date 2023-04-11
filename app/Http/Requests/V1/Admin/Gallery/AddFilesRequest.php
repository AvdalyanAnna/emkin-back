<?php

namespace App\Http\Requests\V1\Admin\Gallery;

use App\Http\Requests\V1\FormRequest;

/**
 * Class AddFilesRequest
 * @package App\Http\Requests\V1\Admin\Gallery
 */
class AddFilesRequest extends FormRequest {

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
           'custom_properties' => 'nullable|array',
           'custom_properties.alt' => 'nullable|string',
           'custom_properties.header' => 'nullable|string',
           'custom_properties.description' => 'nullable|string|max:2500',
           'files' => 'nullable|array|max:10',
           'files.*' => 'required|mimes:jpeg,jpg,png|max:2560', // 2.5 mb
       ];
    }
}
