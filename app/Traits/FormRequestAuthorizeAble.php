<?php

namespace App\Traits;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

/**
 * Trait ApiResponseAble
 * @package App\Traits
 */
trait FormRequestAuthorizeAble {

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {

        if (!$this->exists('confirmation_type')) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    'confirmation_auth' => 'The provided credentials are incorrect. Please write a phone number or email',
                ]
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY));
        }

        return true;
    }
}
