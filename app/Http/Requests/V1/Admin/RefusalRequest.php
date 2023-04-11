<?php

namespace App\Http\Requests\V1\Admin;

use App\Http\Requests\V1\FormRequest;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Class RefusalRequest
 * @package App\Http\Requests\V1\Admin\Commercial
 */
class RefusalRequest extends FormRequest {

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        return true;
    }

    /**
     * @return string[]
     */
    #[ArrayShape(['id' => "string", 'refusal' => "string", 'order' => "string"])]
    public function rules(): array {
        return [
            'id' => 'nullable|exists:refusals',
            'refusal' => 'required|string|max:1000|min:2',
            'order' => 'nullable|numeric',
        ];
    }
}
