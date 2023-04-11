<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TimezoneMiddleware {

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @throws \Exception
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $default_timezone = 'UTC';
        $timezone_cookie = \Cookie::get('timezone');
        $visitor_timezone = geoip()->getLocation(request()->ip())->timezone;

        if (($timezone_cookie === null) || (!in_array($timezone_cookie, \DateTimeZone::listIdentifiers()))) {
            $this->setTimezone($visitor_timezone);
            return $next($request)->withCookie(cookie()->forever('timezone', $visitor_timezone));
        } else if (in_array($timezone_cookie, \DateTimeZone::listIdentifiers())) {
            $this->setTimezone($timezone_cookie);
            return $next($request)->withCookie(cookie()->forever('timezone', $timezone_cookie));
        }

        $this->setTimezone($default_timezone);
        return $next($request)->withCookie(cookie()->forever('timezone', $default_timezone));
    }

    public function setTimezone($timezone) {
        config(['app.timezone' => $timezone]);
        date_default_timezone_set($timezone);
    }
}
