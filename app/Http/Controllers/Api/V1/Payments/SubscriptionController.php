<?php

namespace App\Http\Controllers\Api\V1\Payments;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Users\PaymentRequest;
use App\Services\V1\Users\SubscriptionService;
use App\Traits\ApiResponseAble;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;

/**
 * Class SubscriptionController
 * @package App\Http\Controllers\Api\V1\Users
 */
class SubscriptionController extends Controller {

    use ApiResponseAble;

    /**
     * AuthController constructor.
     * @param SubscriptionService $subscriptionService
     */
    public function __construct(private SubscriptionService $subscriptionService) { }

    /**
     * @param PaymentRequest $request
     * @throws \Throwable
     * @return JsonResponse
     */
    public function createSession(PaymentRequest $request): JsonResponse {
        try {
            $checkout = $this->subscriptionService->cretePaymentSession($request->user(), $request->all());
            return $this->success(['checkout_url' => $checkout->url]);
        } catch (ApiErrorException | \Exception $e) {
            Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }
}
