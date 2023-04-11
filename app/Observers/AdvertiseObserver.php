<?php

namespace App\Observers;

use App\Enums\Advertise\AdvertiseStatus;
use App\Models\Advertise;
use Carbon\Carbon;

/**
 * Class AdvertiseObserver
 * @package App\Observers
 */
class AdvertiseObserver {

    /**
     * Handle the Advertise "created" event.
     *
     * @param Advertise $advertise
     * @return void
     */
    public function created(Advertise $advertise) {
        $this->sendCreatedNotification($advertise);
    }

    /**
     * Handle the Advertise "updated" event.
     *
     * @param Advertise $advertise
     * @return void
     */
    public function updated(Advertise $advertise) {
        $this->eventForStatusUpdated($advertise);
    }

    /**
     * Handle the Advertise "deleted" event.
     *
     * @param Advertise $advertise
     * @return void
     */
    public function deleted(Advertise $advertise) { }

    /**
     * Handle the Advertise "restored" event.
     *
     * @param Advertise $advertise
     * @return void
     */
    public function restored(Advertise $advertise) { }

    /**
     * Handle the Advertise "force deleted" event.
     *
     * @param Advertise $advertise
     * @return void
     */
    public function forceDeleted(Advertise $advertise) { }

    /**
     * @param Advertise $advertise
     */
    protected function sendCreatedNotification(Advertise $advertise): void {
        $advertise->author->notify(new \App\Notifications\V1\Advertise([
            'title' => $advertise->name,
            'event' => __FUNCTION__,
            'message' => $advertise->description
        ]));
    }

    /**
     * @param Advertise $advertise
     */
    protected function eventForStatusUpdated(Advertise $advertise) : void {

        if ($advertise->isDirty('status')) {

            $status = +$advertise->status;

            /**
             * Статус подтверждённого обновления (1)
             */
            $active = AdvertiseStatus::fromValue(AdvertiseStatus::Active);

            if ($active->is($status)) {

                /**
                 * Отпровляем уведомление что обнавление подтверждено
                 */
                $advertise->author->notify(new \App\Notifications\V1\Advertise([
                    'title' => $advertise->name,
                    'event' => __FUNCTION__,
                    'subject' => __('messages.advertise.ADVERTISE_ACCEPTED'),
                    'message' => $advertise->description
                ]));

                /**
                 * продлеваем срок обновления до 30 дней
                 */
                \DB::table('advertises')->where('id','=', $advertise->id)->update([
                    'inactively_date' => Carbon::now()->addDays(30)
                ]);
            }

            /**
             * Статус отклонённого обновления (3)
             */
            $rejected = AdvertiseStatus::fromValue(AdvertiseStatus::Rejected);

            if ($rejected->is($status)) {
                /**
                 * Отпровляем уведомление что обнавление отклонено
                 */
                $advertise->author->notify(new \App\Notifications\V1\Advertise([
                    'title' => $advertise->name,
                    'subject' => __('messages.advertise.ADVERTISE_REJECTED'),
                    'event' => __FUNCTION__,
                    'message' => $advertise?->reason_for_refusal?->refusal ?? $advertise->refusal_comment
                ]));
            }
        }
    }
}
