<?php

namespace App\Providers;

use App\Listeners\ChargeSource;
use App\Listeners\CheckoutSessionCompleted;
use App\Listeners\CheckoutSessionExpired;
use App\Listeners\PaymentFailed;
use App\Listeners\PaymentSucceeded;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        'stripe-webhooks::checkout.session.async_payment_succeeded' => [
            PaymentSucceeded::class,
        ],
        'stripe-webhooks::checkout.session.async_payment_failed' => [
            PaymentFailed::class,
        ],
        'stripe-webhooks::checkout.session.expired' => [
            CheckoutSessionExpired::class,
        ],
        'stripe-webhooks::charge.refunded' => [
            ChargeSource::class,
        ],
        'stripe-webhooks::checkout.session.completed' => [
            CheckoutSessionCompleted::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
