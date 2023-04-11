<?php

namespace App\Http\Requests\V1;

/**
 * Class LanguageRequest
 * @package App\Http\Requests\V1
 */
class LanguageRequest extends FormRequest
{
    /**
     *|---------------------------------------------------------------------------
     *| Validation Rules for Validatable trait
     *|---------------------------------------------------------------------------
     *| @var array[] $rules
     */
    protected array $rules = [
        'POST' => [
            'name' => ['required', 'max:50'],
            'native' => ['required', 'max:50'],
            'code' => ['required', 'max:10'],
            'regional' => ['required', 'max:10'],
            'default' => ['nullable', 'boolean'],
        ],
        'PUT' => [
            'id' => ['required', 'exists:languages,id'],
            'name' => ['sometimes', 'string', 'max:50'],
            'native' => ['sometimes', 'string', 'max:50'],
            'code' => ['sometimes', 'string', 'max:10'],
            'regional' => ['sometimes', 'string', 'max:10'],
            'default' => ['sometimes', 'boolean'],
        ],
    ];

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        return $this->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array {
        return $this->rules[$this->getMethod()];
    }
}
