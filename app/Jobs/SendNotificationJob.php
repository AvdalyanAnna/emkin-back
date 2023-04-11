<?php

namespace App\Jobs;

use App\Contracts\MakeStaticInstance;
use App\Models\User;
use App\Notifications\V1\ActionNotifications;
use App\Traits\MakeStaticInstanceAble;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class SendNotificationJob
 * @package App\Jobs
 */
class SendNotificationJob implements ShouldQueue, MakeStaticInstance
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, MakeStaticInstanceAble;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(private array $data) { }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        User::whereHas('roles', function($query) {
            $query->whereName('user');
        })->chunk(1000, function ($users) {
            foreach ($users as $user) $user->notify(ActionNotifications::make($this->data));
        });
    }
}
