<?php

namespace App\Providers;

use Prettus\Repository\Providers\RepositoryServiceProvider as RepositoryServiceProviderAlias;

/**
 * Class RepositoryServiceProvider
 * @package App\Providers
 */
class RepositoryServiceProvider extends RepositoryServiceProviderAlias {

    /**
     * Register services.
     *
     * @return void
     */
    public function register() {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot() {
        $this->bindProviders(config('app.api.version'));
    }

    /**
     * @param $version
     * @return void
     */
    public function bindProviders($version): void {
        if (key_exists($version, config('repository.repositories'))) {
            foreach (config('repository.repositories')[$version] as $repositoryContract => $repositoryClass) {
                $this->app->bind($repositoryContract, $repositoryClass);
            }
        }
    }
}
