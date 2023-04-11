<?php

namespace App\Providers;

use App\Models\Advertise;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Observers\AdvertiseObserver;
use App\Observers\ConversationObserver;
use App\Observers\MessageObserver;
use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;

/**
 * Class ObserveProvider
 * @package App\Providers
 */
class ObserveProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        User::observe(UserObserver::class);
        Advertise::observe(AdvertiseObserver::class);
        Conversation::observe(ConversationObserver::class);
//        Message::observe(MessageObserver::class);
//        Participation::observe(ParticipationObserver::class);
    }
}
