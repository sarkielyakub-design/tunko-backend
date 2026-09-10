<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\Reloadly\Support\ReloadlyConfig;
use App\Services\Reloadly\Auth\ReloadlyAuthService;
use App\Services\Reloadly\Http\ReloadlyHttpClient;
use App\Services\Reloadly\Client\ReloadlyClient;

use App\Contracts\Providers\AirtimeProviderInterface;
use App\Services\Reloadly\ReloadlyAirtimeProvider;

class ReloadlyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ReloadlyConfig::class);

        $this->app->singleton(ReloadlyAuthService::class);

        $this->app->singleton(ReloadlyHttpClient::class);

        $this->app->singleton(ReloadlyClient::class);

        $this->app->bind(
            AirtimeProviderInterface::class,
            ReloadlyAirtimeProvider::class
        );
    }

    public function boot(): void
    {
        //
    }
}