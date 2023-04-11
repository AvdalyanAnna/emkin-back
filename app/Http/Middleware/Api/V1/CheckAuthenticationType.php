<?php

namespace App\Http\Middleware\Api\V1;

use App\Models\Notification;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Class CheckAuthenticationType
 * @package App\Http\Middleware\Api\V1
 */
class CheckAuthenticationType
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed {
        $confirmation = $request->get('confirmation_auth');

        if (is_numeric($confirmation)) {
            $request->merge([
                'confirmation_type' => Notification::SMS_CONFIRMATION,
                'phone' => $confirmation,
                'field' => 'phone',
            ]);
        }

        if (filter_var($confirmation, FILTER_VALIDATE_EMAIL)) {
            $request->merge([
                'confirmation_type' => Notification::Email_CONFIRMATION,
                'email' => $confirmation,
                'field' => 'email',
            ]);
        }

        return $next($request);
    }
}
