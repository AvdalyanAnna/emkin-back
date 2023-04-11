<?php

namespace App\Providers;

use App\Models\Conversation;
use App\Models\Media;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider {

    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /** @var string $apiNamespace */
    protected string $apiNamespace = "App\\Http\\Controllers\\Api";

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot() {
        $this->configureRateLimiting();
        $this->enableBindings();

        $version = \request()->header('accept-version') ?? config('app.api.latest');

        if ($version && !in_array($version, config('app.api.versions'))) {
            $version = config('app.api.latest');
        }

        config(['app.api.version' => $version]);

        $this->routes(function () use ($version) {

            Route::middleware('api')
                ->prefix('api')
                ->namespace("$this->apiNamespace\\$version")
                ->group(base_path("routes/api.php"));

            Route::middleware('api')
                ->prefix('api/admin')
                ->namespace("$this->apiNamespace\\$version\\Admin")
                ->group(base_path("routes/api_admin.php"));

            if (config('chat.should_load_routes')) {
                Route::middleware(config('chat.routes.middleware'))
                    ->prefix(config('chat.routes.path_prefix'))
                    ->namespace("$this->apiNamespace\\$version\\Chat")
                    ->group(base_path("routes/chat.php"));
            }

            Route::middleware('web')->group(base_path('routes/web.php'));
        });
    }

    /**
     * @return void
     */
    public function enableBindings(): void {
        Route::bind('user', function($id) {
            return User::withoutGlobalScopes()->findOrFail($id);
        });
        Route::bind('conversation', function($id) {
            return Conversation::withoutGlobalScopes()->findOrFail($id);
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting() {
        $this->configureRateLimitingForStatistics();
    }

    public function configureRateLimitingForStatistics(): void {
        foreach ([
            'favorite',
            'show_details',
            'phone_view',
        ] as $key) {
            RateLimiter::for($key, function (Request $request) {
                return Limit::perMinutes(120, 1)->by($request->user()?->id ?: $request->ip());
            });
        }
    }
}
