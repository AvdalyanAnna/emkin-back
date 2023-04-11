<?php

namespace App\Http\Requests\V1\Admin\Category;

use App\Http\Requests\V1\FormRequest;

/**
 * Class FilterRequest
 * @package App\Http\Requests\V1\Admin\Category
 */
class FilterRequest extends FormRequest {

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {

        $filters = $this->get('filters');

        foreach ($filters as $key => $item) {
            if (!isset($item['id'])) $filters[$key]['id'] = null;
        }

        $this->merge(['filters' => $filters]);

        return user()->isAdmin();
    }

    /**
     * @var array|\string[][]
     */
    public array $rules = [
        'POST' => [
            'filters' => 'required|array',
            'filters.*.name' => 'required|string|max:50',
            'filters.*.answers' => 'nullable|array',
            'filters.*.category_id' => 'required|exists:categories,id',
            'filters.*.answers.*.name' => 'required|string',
            'filters.*.answers.*.order' => 'required',
        ],
        'PUT' => [
            'filters' => 'required|array',
            'filters.*.category_id' => 'nullable|exists:categories,id',
            'filters.*.id' => 'nullable|exists:filters,id',
            'filters.*.name' => 'nullable|string|max:50',
            'filters.*.answers' => 'nullable|array',
            'filters.*.answers.*.id' => 'nullable|exists:filter_answers',
            'filters.*.answers.*.name' => 'nullable|string',
            'filters.*.answers.*.order' => 'nullable|string',
        ]
    ];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array {
        return $this->rules[$this->getMethod()];
    }
}
