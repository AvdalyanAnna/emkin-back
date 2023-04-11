<?php

namespace App\Http\Middleware\Api\V1;

use App\Exceptions\BlockedUserException;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class CheckerUserBlocked
 * @package App\Http\Middleware\Api\V1
 */
class CheckerUserBlocked
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @throws \Throwable
     * @return JsonResponse
     */
    public function handle(Request $request, Closure $next): JsonResponse {

        if ($participant_id = $request->get('participant_id')) {
            $blockedFromAnother = in_array($request->user()->id, $request->user()->blockedListFromAnotherUser());

            throw_if($blockedFromAnother, BlockedUserException::blockedUser(false));

            $blockedFromCurrent = in_array($participant_id, $request->user()->blockedListFromCurrentUser());

            throw_if($blockedFromCurrent, BlockedUserException::blockedUser());
        }

        return $next($request);
    }
}
