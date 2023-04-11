<?php

namespace App\Services\V1\Users;

use App\Enums\Admin\Moderator\ModeratorStatisticsEnum;
use App\Enums\MediaCollections;
use App\Mail\V1\Support\SendPasswordEmail;
use App\Models\ModeratorStatistic;
use App\Models\SocialAccount;
use App\Models\User;
use App\Repositories\V1\Users\NotificationRepository;
use App\Repositories\V1\Users\UserRepository;
use App\Services\V1\BaseService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Prettus\Repository\Exceptions\RepositoryException;
use Throwable;

/**
 * Class UserService
 * @package App\Services\V1\Users
 */
class UserService extends BaseService {

    /**
     * UserService constructor.
     * @param UserRepository $userRepository
     * @param NotificationRepository $notificationRepository
     */
    public function __construct(
        private UserRepository $userRepository,
        private NotificationRepository $notificationRepository,
    ) { }

    /**
     * @return UserRepository
     */
    public function getRepo(): UserRepository {
        return $this->userRepository;
    }

    /**
     * @return NotificationRepository
     */
    public function notification(): NotificationRepository {
        return $this->notificationRepository;
    }

    /**
     * @return mixed
     */
    public function model(): Model {
        return $this->getRepo()->getModel();
    }

    /**
     * @param $data
     * @return mixed
     */
    public function create($data): mixed {
        $this->model()->getConnectionResolver()->transaction(function () use ($data, &$user) {
            $user = $this->getRepo()->create($data);
            $user->notify(app($data['confirmation_type']));
        });

        return $user;
    }

    /**
     * @param Request $request
     */
    public function sendCodForEmailOrPhone(Request $request): void {
        $user = $request->user();
        $field = $request->get('field');
        $confirmation_type = $request->get('confirmation_type');

        /**
         * Добавляет Номер Или Почт (Это тот случи когда у него нет номера и он добавляет)
         * Будем отправить код на ту же почту или телефон
         */
        if (!$user->{$field}) {
            $user->{$field} = $request->get($field);
        }

        $user->notify(app($confirmation_type));
    }

    /**
     * @param Request $request
     * @param bool $moderator
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @return mixed
     */
    public function getUsers(Request $request, bool $moderator = false) {

        $collection = $this->getRepo();

        if ($moderator) {
            $collection = $collection->whereHas('roles', function($query) {
                $query->whereName('moderator');
            });
        } else {
            $collection = $collection->whereHas('roles', function($query) {
                $query->whereName('user');
            });
        }

        $collection = $collection->withoutGlobalScopes('verified_at');

        return $this->isExportData()
            ? $this->getUsersExportData($collection->get())
            : $collection->paginate($request->query('per_page'));
    }

    /**
     * @return mixed
     */
    public function supportModerators(): mixed {
        return $this->getRepo()->moderatorCan(config('roles.permissions.access_support.permission_name'));
    }

    /**
     * @param $collection
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @return mixed
     */
    public function getUsersExportData($collection): mixed
    {
        return $this->exportData(
            $collection,
            config('export_configs.moderators.headings'),
            function ($item) {
                return collect([
                    $item->id,
                    $item->full_name,
                    $item->email,
                    $item->phone,
                    $item->created_at,
                ]);
            }
        );
    }

    /**
     * @param Request $request
     * @param int $moderator_id
     */
    public function banUser(Request $request, int $moderator_id){
        $banned = +$request->get('type');
        $banned_ids = $request->get('banned_ids');
        \DB::table('users')->whereIn('id', $banned_ids)->update(['banned' => $banned]);

        /**
         * Сохроняем статистику для модератора
         */
        foreach ($banned_ids as $banned_id) {
            $statistic = ['moderator_id' => $moderator_id];

            if ($banned) {
                $statistic['banned_id'] = $banned_id;
                $statistic['type'] = ModeratorStatisticsEnum::BANNED_USERS;
            } else {
                $statistic['unbanned_id'] = $banned_id;
                $statistic['type'] = ModeratorStatisticsEnum::UNBANNED_USERS;
            }

            ModeratorStatistic::make($statistic);
        }
    }

    /**
     * Create a user and social account if does not exist
     *
     * @param $providerName string
     * @param $providerUser
     * @throws Throwable
     * @return User
     */
    public function firstOrCreateUserAndSocialAccount(string $providerName, $providerUser): User {

        $this->model()->getConnectionResolver()->transaction(function () use ($providerName, $providerUser, &$user) {
            $emailProvider = $providerUser->getEmail();

            $social = SocialAccount::firstOrNew([
                'provider_user_id' => $providerUser->getId(),
                'provider_email' => $emailProvider,
                'provider' => $providerName
            ]);

            if ($social->exists) {
                $social = $social->load('user');
                $user = $social->user;
            } else {

                if (!$user = $this->getRepo()->findByField('email', $emailProvider)->first()) {
                    $fullName = $providerUser->getName();
                    $dataUser = ['email' => $emailProvider, 'first_name' => $fullName];
                    $this->checkProviderUserFullName($dataUser, $fullName);
                    $pass = fake()->password(8);

                    $user = $this->getRepo()->create(array_merge($dataUser, [
                        'password' => $pass,
                        'verified_at' => now(),
                    ]));

                    $user->assignRole('user');

                    \Mail::to($emailProvider)->send(new SendPasswordEmail($fullName, $pass));
                }

                $social->user()->associate($user);
                $social->save();
            }
        });

        return $user;
    }

    /**
     * Проверяем если Пользователь ввел правильны код подтверждения верифицируем его
     *
     * @param array $data
     * @throws RepositoryException
     * @return User|null
     */
    public function verifyUserIfValidCode(array $data): ?User {
        $userResult = $this->model()->getConnectionResolver()->transaction(function () use ($data) {

            $hasResetPassword = isset($data['reset_password']);
            $field = $data['field'];

            $user = !$hasResetPassword
                ? $this->getRepo()->getUserByEmailOrPhone($data)
                : $this->getRepo()->findWhere([$field => $data[$field]])->firstOrFail();

            $notifyForConfirmation = $this->notification()->getExistsConfirmationNotification(
                $user,
                $data['code']
            );

            if ($notifyForConfirmation) {
                $notifyForConfirmation->markAsRead();
                if (!$hasResetPassword) {
                    $user->update(['verified_at' => now()]);
                }
            }

            return $notifyForConfirmation ? $user: null;
        });

        return $userResult;
    }

    /**
     * @param array $attributes
     * @return User
     */
    public function addModerator(array $attributes): User {
        $user = $this->getRepo()->addUser($attributes, MediaCollections::MODERATOR_USER_AVATAR_COLLECTION);
        $user->assignRole('moderator');
        return $user;
    }

    /**
     * @param array $dataUser
     * @param string $fullName
     */
    public function checkProviderUserFullName(array &$dataUser, string $fullName): void {
        if (isFullName($fullName)) {
            $fullName = explode(' ', $fullName);
            $dataUser = array_merge($dataUser, [
                'first_name' => $fullName[0],
                'last_name' => $fullName[1]
            ]);
        }
    }
}
