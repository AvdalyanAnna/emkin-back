<?php

namespace App\Listeners;

use App\Enums\Users\SubscriptionStatuses;
use App\Repositories\V1\Users\SubscriptionRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Spatie\WebhookClient\Models\WebhookCall;

/**
 * Class CheckoutSessionCompleted
 * @package App\Listeners
 */
class CheckoutSessionCompleted implements ShouldQueue
{
    /**
     * @param WebhookCall $webhookCall
     */
    public function handle(WebhookCall $webhookCall)
    {
        $metaData = $webhookCall->payload['data']['object']['metadata'];

        $subscriptionData = array_merge([
            'stripe_id' => $webhookCall->payload['id'],
            'status' => SubscriptionStatuses::ACTIVE,
        ], $metaData);

        Log::info('CheckoutSessionCompleted => ' . \Carbon\Carbon::now()->toDateString());
//        Log::error(json_encode($webhookCall->payload));
//        Log::alert(print_r($subscriptionData));
        app(SubscriptionRepository::class)->create($subscriptionData);
    }
}
