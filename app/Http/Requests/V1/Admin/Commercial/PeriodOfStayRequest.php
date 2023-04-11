<?php

namespace App\Http\Requests\V1\Admin\Commercial;

use App\Http\Requests\V1\FormRequest;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Class PeriodOfStayRequest
 * @package App\Http\Requests\V1\Admin\Commercial
 */
class PeriodOfStayRequest extends FormRequest {

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
    #[ArrayShape([
        'id' => "string",
        'title' => "string",
        'status' => "string",
        'order' => "string"
    ])] public function rules(): array {
        return [
            'id' => 'nullable|exists:period_of_stays',
            'title' => 'required|string|max:100|min:2',
            'status' => 'required|boolean',
            'order' => 'nullable|numeric',
        ];
    }
}
