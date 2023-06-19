<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        
        $this->app->bind(\App\Repositories\AuthenticationRepository::class, \App\Repositories\AuthenticationRepositoryEloquent::class);
        $this->app->bind(\App\Repositories\SetupRepository::class, \App\Repositories\SetupRepositoryEloquent::class);
        $this->app->bind(\App\Repositories\StoreRepository::class, \App\Repositories\StoreRepositoryEloquent::class);
        $this->app->bind(\App\Repositories\CustomerSubscriptionRepository::class, \App\Repositories\CustomerSubscriptionRepositoryEloquent::class);
        $this->app->bind(\App\Repositories\PackageRepository::class, \App\Repositories\PackageRepositoryEloquent::class);
        $this->app->bind(\App\Repositories\PackageSubscriptionRepository::class, \App\Repositories\PackageSubscriptionRepositoryEloquent::class);
        $this->app->bind(\App\Repositories\StoreBalanceRepository::class, \App\Repositories\StoreBalanceRepositoryEloquent::class);
        $this->app->bind(\App\Repositories\TransferHistoryRepository::class, \App\Repositories\TransferHistoryRepositoryEloquent::class);
        //:end-bindings:
    }
}
