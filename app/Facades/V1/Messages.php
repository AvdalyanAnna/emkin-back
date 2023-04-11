<?php

namespace App\Facades\V1;

use App\Models\Notification as NotificationModel;

/**
 * Class Messages
 * @package App\Facades\V1
 */
class Messages {

    /**
     * @param string $typeMessage
     * @return string
     */
    function generateConfirmMessages(string $typeMessage): string {
        return [
            NotificationModel::Email_CONFIRMATION => __('messages.EMAIL_CONFIRMATION_SUCCESS'),
            NotificationModel::SMS_CONFIRMATION => __('messages.SMS_CONFIRMATION_SUCCESS'),
        ][$typeMessage];
    }
}
