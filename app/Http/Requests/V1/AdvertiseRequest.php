<?php

namespace App\Http\Requests\V1;

use App\Enums\Advertise\AdvertiseStatus;
use Illuminate\Support\Facades\Auth;

/**
 * Class AdvertiseRequest
 * @package App\Http\Requests\V1
 */
class AdvertiseRequest extends FormRequest {

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {

        if ($this->has('status')) {

            $status = $this->get('status');

            /**
             *  черновик (5)
             */
            $draft = AdvertiseStatus::fromValue(AdvertiseStatus::Draft);
            $NotVerified = AdvertiseStatus::fromValue(AdvertiseStatus::NotVerified);
            if (!$draft->is(+$status) && !$NotVerified->is(+$status)) {
                $this->request->remove('status');
            }
        }

        return Auth::check();
    }

    /**
     *|---------------------------------------------------------------------------
     *| Validation Rules for Validatable trait
     *|---------------------------------------------------------------------------
     *| @var array[] $rules
     */
    protected array $rules = [
        'POST' => [
            'pictures' => 'nullable|array|max:10',
            'pictures.*.file' => 'nullable|mimes:jpeg,jpg,png|max:2560', // 2.5 mb
            'pictures.*.media_id' => 'nullable|exists:media,id',
            'pictures.*.order' => 'required|numeric',
            'name' => 'required|string|max:255|min:2',
            'description' => 'required|string|max:5000|min:20',
            'refusal_comment' => 'nullable|string|max:1000|min:20',
            'price' => 'required|numeric',
            'latitude' => 'required|string',
            'longitude' => 'required|string',
            'contacts' => 'nullable|numeric|between:0,2',
            'contact_phone_numeric' => 'required_unless:contacts,!=,2|numeric',
            'contact_phone' => 'required_unless:contacts,!=,2|string',
            'address' => 'required|string|max:255',
            'link' => 'nullable|string',
            'type' => 'required|numeric|between:0,1',
            'price_policy' => 'required|numeric|between:0,2',
            'auto_renewal' => 'nullable|boolean',
            'available_cost' => 'nullable|boolean',
            'show_details' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'city_id' => 'required|exists:cities,id',
            'answers' => 'nullable|array',
            'answers.*' => 'required|exists:filter_answers,id',
        ],
        'PUT' => [
            'pictures' => 'nullable|array|max:10',
            'pictures.*.file' => 'nullable|mimes:jpeg,jpg,png|max:2560', // 2.5 mb
            'pictures.*.media_id' => 'nullable|exists:media,id',
            'pictures.*.order' => 'required|numeric',
            'name' => 'nullable|string|max:255|min:2',
            'description' => 'nullable|string|max:5000|min:20',
            'price' => 'nullable|numeric',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
            'refusal_comment' => 'nullable|string|max:1000|min:20',
            'address' => 'nullable|string|max:255',
            'link' => 'nullable|string',
            'contact_phone' => 'nullable|string',
            'auto_renewal' => 'nullable|boolean',
            'contact_phone_numeric' => 'nullable|numeric',
            'available_cost' => 'nullable|boolean',
            'show_details' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'city_id' => 'nullable|exists:cities,id',
            'answers' => 'nullable|array',
            'answers.*' => 'required|exists:filter_answers,id',
            'type' => 'nullable|numeric|between:0,1',
            'price_policy' => 'nullable|numeric|between:0,2',
            'contacts' => 'nullable|numeric|between:0,2',
        ],
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
