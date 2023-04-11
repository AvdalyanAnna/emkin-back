<?php

namespace App\Http\Requests\V1\Admin\Gallery;

use App\Http\Requests\V1\FormRequest;

/**
 * Class MediaFilesRequest
 * @package App\Http\Requests\V1\Admin\Gallery
 */
class MediaFilesRequest extends FormRequest {

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
           'media_ids' => 'array|required',
           'media_ids.*' => 'required|exists:media,id',
       ];
    }
}
