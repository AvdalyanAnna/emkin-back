<?php

namespace App\Exceptions;

use App\Exceptions\Chat\ConversationIncorrectParticipantException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\ItemNotFoundException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class Handler
 * @package App\Exceptions
 */
class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->renderable(function (NotFoundHttpException | ItemNotFoundException $e, $request) {
            if ($request->is('api/*')) {
                return new JsonResponse([
                    'errors' => [
                        'message' => $e->getMessage() ?: 'Record not found'
                    ],
                ], method_exists($e,'getStatusCode') ? $e->getStatusCode() : 422);
            }
        });

        $this->renderable(function (BlockedUserException | ConversationIncorrectParticipantException $e) {
            return new JsonResponse([
                'errors' => [
                    'message' => $e->getMessage() ?: 'Record not found'
                ],
            ], method_exists($e,'getStatusCode') ? $e->getStatusCode() : 422);
        });

        $this->renderable(function (ThrottleRequestsException $e, $request) {
            $secondsRemaining = $e->getHeaders()['Retry-After'];

            if ($request->is('api/*')) {
                return new JsonResponse([
                    'errors' => [
                        'message' => 'Too many request...',
                        'retryAfter' => 'Retry After ' . formatSeconds($secondsRemaining)
                    ],
                ], method_exists($e,'getStatusCode') ? $e->getStatusCode() : 422);
            }
        });

        $this->renderable(function (UnauthorizedException $e) {
            return new JsonResponse([
                'errors' => [
                    'code' => $e->getStatusCode(),
                    'message' => $e->getMessage(),
                ],
            ], $e->getStatusCode());
        });

//      $this->reportable(function (Throwable $e) {});
    }
}
