<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

/**
 * Trait ApiResponseAble
 * @package App\Traits
 */
trait ApiResponseAble {

    /**
     * Response Status Codes From Api
     *
     * @var array[] $statusCodes
     */
    protected static array $statusCodes = [
        'done' => 200,
        'created' => 201,
        'accepted' => 202,
        'removed' => 204,
        'not_modified' => 204,
        'not_valid' => 400,
        'not_found' => 404,
        'forbidden' => 403,
        'unauthorized' => 401,
        'permissions' => 403,
        'unprocessable' => 422,
    ];

    /**
     * Return a success JSON response.
     *
     * @param array|string|object|null $data
     * @param string|null $message
     * @param string|int $code
     * @return JsonResponse
     */
    protected function success(array|string|object $data = null, string $message = null, string|int $code = 200): JsonResponse {
        return response()->json([
            'status' => 'Success',
            'message' => $message,
            'data' => $data
        ], is_string($code) ? self::$statusCodes[$code] : $code);
    }

    /**
     * Return an error JSON response.
     *
     * @param string|null $message
     * @param string|int $code
     * @param null $data
     * @return JsonResponse
     */
    protected function error(string $message = null, string|int $code = 'forbidden', $data = null): JsonResponse {
        return response()->json([
            'status' => 'Error',
            'message' => $message,
        ], is_string($code) ? self::$statusCodes[$code] : $code);
    }

    /**
     * @param $errors
     * @return JsonResponse
     */
    protected function validationResponse($errors): JsonResponse {
        return response()->json([
            'errors' => $errors
        ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @param null $message
     * @param string|int|null $code
     * @return JsonResponse
     */
    protected function clientErrorResponse($message = null, string|int $code = null): JsonResponse
    {
        return response()->json([
            'status' => 'Error',
            'message' => isLocal() ? $message : __('messages.UNPROCESSABLE')
        ], $code ?: self::$statusCodes['unprocessable']);
    }
}
