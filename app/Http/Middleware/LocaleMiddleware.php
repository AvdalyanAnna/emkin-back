<?php

namespace App\Http\Middleware;

use App\Repositories\V1\LanguageRepositoryEloquent;
use App\Traits\ApiResponseAble;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class LocaleMiddleware
 * @package App\Http\Middleware
 */
class LocaleMiddleware {

    use ApiResponseAble;

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next) {
        $locale = $this->parseHttpLocale($request);

        $locales = app(LanguageRepositoryEloquent::class)->all(['code'])->pluck('code')->toArray();

        if (!in_array($locale, $locales)) {
            $this->error('is not supported this locale', Response::HTTP_BAD_REQUEST);
        }

        if ($locale = $this->parseHttpLocale($request)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }

    /**
     * @param Request $request
     * @return string
     */
    private function parseHttpLocale(Request $request): string {
        $list = explode(',', $request->server('HTTP_ACCEPT_LANGUAGE', app()->getLocale()));

        $locales = Collection::make($list)->map(function ($locale) {
                $parts = explode(';', $locale);

                $mapping['locale'] = trim($parts[0]);

                if (isset($parts[1])) {
                    $factorParts = explode('=', $parts[1]);

                    $mapping['factor'] = $factorParts[1];
                } else {
                    $mapping['factor'] = 1;
                }

                return $mapping;
            })->sortByDesc(function ($locale) {
                return $locale['factor'];
            });

        return $locales->first()['locale'];
    }
}
