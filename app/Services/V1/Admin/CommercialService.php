<?php

namespace App\Services\V1\Admin;

use App\Jobs\SendNotificationJob;
use App\Models\CommercialNotification;

/**
 * Class CommercialService
 * @package App\Services\V1\Users
 */
class CommercialService  {

    /**
     * @param CommercialNotification $notification
     * @return void
     */
    public function sendNotification(CommercialNotification $notification): void {
        SendNotificationJob::dispatch([
            'title' => $notification->getTranslations('title'),
            'description' => $notification->getTranslations('description'),
            'details' => $notification->getTranslations('details'),
            'link' => $notification->link,
        ]);
    }
}
