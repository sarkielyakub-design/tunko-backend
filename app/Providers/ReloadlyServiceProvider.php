<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Reloadly\ReloadlyConfig;
use App\Services\Reloadly\ReloadlyAuthService;

class ReloadlyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            ReloadlyConfig::class
        );

        $this->app->singleton(
            ReloadlyAuthService::class
        );
        $this->app->singleton(
    ReloadlyHttpClient::class
);
$this->app->singleton(
    ReloadlyClient::class
);
$this->app->bind(

    \App\Contracts\Providers\AirtimeProviderInterface::class,

    \App\Services\Reloadly\ReloadlyAirtimeProvider::class

);
    }

    public function boot(): void
    {
    }
}