<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Auth\AuthRequest;
use App\Http\Requests\V1\Users\ConfirmationCodeRequest;
use App\Http\Requests\V1\Users\ResetPasswordRequest;
use App\Http\Requests\V1\Users\SendCodeAgainRequest;
use App\Http\Resources\V1\User\UserResource;
use App\Models\User;
use App\Repositories\V1\Users\UserRepositoryEloquent;
use App\Services\V1\Users\UserService;
use App\Traits\ApiResponseAble;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\ItemNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AuthController
 * @package App\Http\Controllers\Auth
 */
class AuthController extends Controller {

    use ApiResponseAble;

    /**
     * AuthController constructor.
     * @param UserRepositoryEloquent $userRepository
     * @param UserService $userService
     */
    public function __construct(private UserRepositoryEloquent $userRepository, private UserService $userService) { }

    /**
     * @param AuthRequest $request
     * @throws \Throwable
     * @return JsonResponse
     */
    public function register(AuthRequest $request): JsonResponse {
        try {
            $user = $this->userService->create($request->all());

            $user->assignRole('user');

            if ($request->exists('email') && $link = parse_email($request->get('email'))) {
                $response['link'] = $link;
            }

            return $this->success($response ?? [],
                \Messages::generateConfirmMessages($request->get('confirmation_type'))
            );
        } catch (\Exception $e) {
            \Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }

    /**
     * @param SendCodeAgainRequest $request
     * @return JsonResponse
     */
    public function login(SendCodeAgainRequest $request): JsonResponse {
        try {
            $field = $request->get('field');

            $user = $this->userRepository
                ->withoutGlobalScopes('verified_at')
                ->where([$field => $request->get($field)])
                ->first();

            if (!$user->verified_at) {
                return $this->clientErrorResponse(__('messages.NOT_VERIFIED_USER'),403);
            }

            if (!\Auth::attempt($request->only([$field, 'password']))) {
                return response()->json([
                    'status' => false,
                    'message' => __('messages.CAN_NOT_REGISTER')
                ], Response::HTTP_UNAUTHORIZED);
            }

            $user->loadCount('unread_notifications');
            $user->load('avatar');

            return $this->success([
                'token_type' => 'Bearer',
                'user' => UserResource::make($user),
                'access_token' => $user->createAccessToken()
            ]);
        } catch (\Exception $e) {
            \Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }

    /**
     * @param $provider
     * @throws \Throwable
     * @return RedirectResponse
     */
    public function callback($provider): RedirectResponse {
        try {
            $providerUser = \Socialite::driver($provider)->stateless()->user();
            $user = $this->userService->firstOrCreateUserAndSocialAccount($provider, $providerUser);
            \Auth::login($user);
            return redirect()->to(config('app.front_web_url') . 'auth/login?token=' . $user->createAccessToken());
        } catch (\Exception $e) {
            \Log::error($e);
            return redirect()->to(config('app.front_web_url') . 'auth/registration?status=error');
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function checkConfirmTypeExists(Request $request): JsonResponse {
        try {
            $validator = \Validator::make([
                'field' => $field = $request->query('field'),
                'value' => $value = $request->query('value'),
            ],[
                'field' => 'required|string|in:email,phone',
                'value' => 'string|max:100|min:1'
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $exists = $this->userRepository->findWhere([$field => $value])->first();

            return response()->json([$field => (bool)$exists]);
        } catch (ValidationException $e) {
            return $this->validationResponse($e->errors());
        } catch (\Exception $e) {
            \Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }

    /**
     * @param $provider
     * @return JsonResponse
     */
    public function socialRedirect($provider): JsonResponse {
        return response()->json([
            'url' => \Socialite::driver($provider)->stateless()->redirect()->getTargetUrl()
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request): \Illuminate\Http\Response {
        \Auth::guard('web')->logout();

        $request->user()->tokens()->delete();

        return response()->noContent();
    }

    /**
     * Проверяем если Пользователь ввел правильны код подтверждения верифицируем его
     *
     * @param ConfirmationCodeRequest $request
     * @return JsonResponse
     */
    public function checkConfirmCodeAndLogin(ConfirmationCodeRequest $request): JsonResponse {
        try {
            $user = $this->userService->verifyUserIfValidCode($request->all());

            if (!$user instanceof User) {
                return $this->error('Your Code is incorrect', Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            \Auth::login($user);

            return $this->success([
                'token_type' => 'Bearer',
                'user' => $user->loadCount('unread_notifications'),
                'access_token' => $user->createAccessToken(
                    $request->has('reset_password') ? [User::RESET_PASSWORD_ABILITY] : ['*']
                )
            ], __('messages.VERIFIED_SUCCESS'));
        } catch (ItemNotFoundException | ModelNotFoundException $e) {
            return $this->error($e->getMessage(), Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            \Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }

    /**
     * @param SendCodeAgainRequest $request
     * @return JsonResponse
     */
    public function sendResetCode(SendCodeAgainRequest $request): JsonResponse {
        try {
            $field = $request->get('field');
            $typeConfirm = $request->get('confirmation_type');

            $user = $this->userRepository->findWhere([
                $field => $request->get($field)
            ])->firstOrFail();

            $user->notify(app($typeConfirm));

            return $this->success([], \Messages::generateConfirmMessages($typeConfirm));
        } catch (ItemNotFoundException | ModelNotFoundException $e) {
            return $this->error($e->getMessage(), Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            \Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }

    /**
     * @param ResetPasswordRequest $request
     * @return JsonResponse
     */
    public function showResetForm(ResetPasswordRequest $request): JsonResponse {
        try {
            $user = $request->user();
            if ($user->tokenCan(User::RESET_PASSWORD_ABILITY)) {
                $user->update(['password' => $request->get('password')]);
                $user->tokens()->delete();
            }

            return $this->success([], __('messages.CHANGE_PASSWORD'));
        } catch (ItemNotFoundException | ModelNotFoundException $e) {
            return $this->error($e->getMessage(), Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            \Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }

    /**
     * Повторно отправляем код подтверждения
     *
     * @param SendCodeAgainRequest $request
     * @return JsonResponse
     */
    public function sendCodeAgain(SendCodeAgainRequest $request): JsonResponse {
        try {
            $typeConfirm = $request->get('confirmation_type');
            $user = $this->userRepository->getUserByEmailOrPhone($request->all());
            $user->notify(app($typeConfirm));

            return $this->success([], \Messages::generateConfirmMessages($typeConfirm));
        } catch (ItemNotFoundException | ModelNotFoundException $e) {
            return $this->error($e->getMessage(), Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            \Log::error($e);
            return $this->clientErrorResponse($e->getMessage());
        }
    }
}
