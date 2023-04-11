<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Class FacadeHelperServiceProvider
 * @package App\Providers
 */
class FacadeHelperServiceProvider extends ServiceProvider {

    /**
     * @var array|string[]
     */
    private array $projectFacadeClasses = [
        'messages' => \App\Facades\V1\Messages::class,
        'chat' => \App\Facades\V1\Chat::class,
    ];

    /**
     * Register services.
     *
     * @return void
     */
    public function register() {
        $this->enableProjectFacades();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot() { }

    /**
     * Активирует Фасади Проекта
     * @return void
     */
    public function enableProjectFacades(): void {
        foreach ($this->projectFacadeClasses as $abstractFacadeName => $facadeClass)
            $this->app->bind($abstractFacadeName, function () use ($facadeClass) { return new $facadeClass; });
    }
}
