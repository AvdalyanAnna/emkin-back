<?php

namespace App\Repositories\V1\Users;

use App\Models\Notification;
use App\Models\User;
use App\Repositories\V1\Base\BaseRepository;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Class NotificationRepositoryEloquent.
 *
 * @package namespace App\Repositories\Users;
 */
class NotificationRepositoryEloquent extends BaseRepository implements NotificationRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model(): string {
        return \App\Models\Notification::class;
    }

    /**
     * @param $user
     * @param $code
     * @return bool
     */
    public function checkConfirmationCode($user, $code): bool {
        $notifyForConfirmation = $this->getExistsConfirmationNotification($user, $code);

        if (!$notifyForConfirmation) return false;

        $notifyForConfirmation->markAsRead();

        return true;
    }

    /**
     * @param $user
     * @param $code
     * @return mixed
     */
    public function getExistsConfirmationNotification($user, $code): mixed {
        return $this->getModel()->newQuery()
            ->where(function ($query) use ($code) {
                $query->where('type', '=', Notification::Email_CONFIRMATION);
                $query->orWhere('type', '=', Notification::SMS_CONFIRMATION);
            })
            ->where([
                ['data->code', '=', $code],
                ['notifiable_id', '=', $user->id],
                ['created_at', '>', Carbon::now()->subMinutes(5)->toDateTimeString()] // Код валидно до 5 минут)
            ])
            ->whereNull('read_at')
            ->orderByDesc('created_at')
            ->first();
    }

    /**
     * @param $request
     * @return LengthAwarePaginator
     */
    public function getNotifications($request): LengthAwarePaginator {
        $tab = $request->query('tab');

        $conditionWheres = [
            'notifiable_id' => user()->id,
            'notifiable_type' => User::class,
        ];

        $query = $this->getModel()->newQuery();

        $query->when(($tab && $tab === 'new'), function ($q) {
            $q->whereNull('read_at')->orderByDesc('created_at');
        });

        $query->when(($tab && $tab === 'old'), function ($q) {
            $q->whereNotNull('read_at')->orderByDesc('created_at');
        });

        $query->when(($tab && $tab === 'all'), function ($q) {
            $q->orderByDesc('created_at');
        });

        return $query->where($conditionWheres)->paginate($request->query('per_page'));
    }

    /**
     * @param array $ids
     */
    public function markAsRead(array $ids): void {
        DB::table('notifications')->whereIn('id', $ids)->update(['read_at' => now()]);
    }
}
