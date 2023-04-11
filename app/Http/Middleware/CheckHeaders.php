<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return JsonResponse
     */
    public function handle(Request $request, Closure $next): JsonResponse {
        $headers = request()->headers->all();

        if (isset($headers['content-type']) && $headers['content-type'][0] !== 'application/vnd.api+json') {
            return response()->json(['message' => 'Unsupported Media Type'], 415);
        }

        return $next($request);
    }
}
