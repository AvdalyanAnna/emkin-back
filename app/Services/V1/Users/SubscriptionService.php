<?php

namespace App\Services\V1\Users;

use App\Models\CommercialUsers;
use App\Models\User;
use App\Repositories\V1\Admin\Commercial\CommercialUserRepository;
use App\Repositories\V1\Users\SubscriptionRepository;
use App\Repositories\V1\Users\UserRepository;
use App\Services\V1\BaseService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Throwable;

/**
 * Class SubscriptionService
 * @package App\Services\V1\Users
 */
class SubscriptionService extends BaseService {

    /**
     * @var \Stripe\StripeClient
     */
    private \Stripe\StripeClient $stripeClient;

    /**
     * UserService constructor.
     * @param UserRepository $userRepository
     * @param SubscriptionRepository $subscriptionRepository
     * @param CommercialUserRepository $commercialUserRepository
     */
    public function __construct(
        private UserRepository $userRepository,
        private SubscriptionRepository $subscriptionRepository,
        private CommercialUserRepository $commercialUserRepository,
    ) { }

    /**
     * @return SubscriptionRepository
     */
    public function getRepo(): SubscriptionRepository {
        return $this->subscriptionRepository;
    }

    /**
     * @return UserRepository
     */
    public function userRepo(): UserRepository {
        return $this->userRepository;
    }

    /**
     * @return CommercialUserRepository
     */
    public function commercialUserRepo(): CommercialUserRepository {
        return $this->commercialUserRepository;
    }

    /**
     * @return mixed
     */
    public function model(): Model {
        return $this->getRepo()->getModel();
    }

    /**
     * @param User $user
     * @param array $data
     * @throws Throwable
     * @return Session
     */
    public function cretePaymentSession(User $user, array $data): Session {

        DB::transaction(function () use (&$checkout, $user, $data) {

            $this->initStripApi();

            $plan = $this->commercialUserRepository->find($data['id']);

            $metaData = $this->generateMetaData($plan, $user);

            /**
             * Создаем Платеж Для Клиента в Стрип
             */
            $checkout = $this->stripeCheckoutCreate($plan, $data, $metaData);
        });

        return $checkout;
    }

    /**
     * TODO Убрать потом Отсюда и поставить в класс (Stripe)
     * @param $plan
     * @param array $data
     * @param array $metaData
     * @throws ApiErrorException
     * @return Session
     */
    private function stripeCheckoutCreate($plan, array $data, array $metaData = []): Session {
        return $this->stripeClient->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'mode' => 'payment',
            'success_url' => $data['success_url'],
            'cancel_url' => $data['cancel_url'],
            'line_items' => [[
                 'price_data' => [
                     'currency' => 'USD',
                     'unit_amount' => $plan->price*100,
                     'product_data' => [
                         'name' => $plan->name,
                         'description' => $plan->description,
                         //'images' => ['https://picsum.photos/200/300'],
                     ],
                 ],
                 'quantity' => 1,
             ]],
            'metadata' => $metaData,
        ]);
    }

    /**
     * TODO Надо Будет поменять реализацию сейчас пока используем Апи Страипа
     */
    private function initStripApi(): void {
        $this->stripeClient = new \Stripe\StripeClient([
            "api_key" => config('stripe-webhooks.signing_secret'),
            "stripe_version" => "2022-08-01"
        ]);
    }

    /**
     * TODO Убрать потом Отсюда и поставить в класс (Stripe)
     *
     * @param $plan
     * @param $user
     * @return array
     */
    public function generateMetaData($plan, $user): array {
        $metaData = [
            'user_id' => $user->id,
            'plan_type' => get_class($plan),
            'plan_id' => $plan->id,
        ];

        if ($plan instanceof CommercialUsers) {
            $metaData['expired_at'] = \Carbon\Carbon::now()->addDays($plan->period_days)->toDateString();
        }

        return $metaData;
    }
}
