<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
 use App\Services\Thunes\Client\ThunesClient;
class ThunesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
{
    $this->app->singleton(
        \App\Services\Thunes\Support\ThunesConfig::class
    );

    $this->app->singleton(
        \App\Services\Thunes\Auth\ThunesAuthService::class
    );

    $this->app->singleton(
        \App\Services\Thunes\Http\ThunesHttpClient::class
    );

    $this->app->singleton(
        \App\Services\Thunes\Client\ThunesClient::class
    );
}

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
   

public function health(
    ThunesClient $client
)
{
    return response()->json(
        $client->health()->json()
    );
}
}
