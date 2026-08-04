<?php

namespace App\Services\Reloadly\Auth;

use App\Services\Reloadly\Support\ReloadlyConfig;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Exception;

class ReloadlyAuthService
{
    private const CACHE_KEY = 'reloadly_access_token';

    public function __construct(
        protected ReloadlyConfig $config
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Get Access Token
    |--------------------------------------------------------------------------
    */

    public function token(): string
    {
        return Cache::remember(
            self::CACHE_KEY,
            now()->addMinutes(55),
            fn () => $this->requestToken()
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Refresh Token
    |--------------------------------------------------------------------------
    */

    public function refresh(): string
    {
        Cache::forget(self::CACHE_KEY);

        return $this->token();
    }

    /*
    |--------------------------------------------------------------------------
    | Authorization Header
    |--------------------------------------------------------------------------
    */

    public function headers(): array
    {
        return [
            'Authorization' => 'Bearer '.$this->token(),
            'Accept' => 'application/com.reloadly.topups-v1+json',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Request OAuth Token
    |--------------------------------------------------------------------------
    */

    protected function requestToken(): string
    {
        $payload = [

            'client_id' => trim(
                $this->config->clientId()
            ),

            'client_secret' => trim(
                $this->config->clientSecret()
            ),

            'grant_type' => 'client_credentials',

            'audience' => trim(
                $this->config->audience()
            ),

        ];

        $response = Http::acceptJson()
            ->asJson()
            ->timeout($this->config->timeout())
            ->post(
                $this->config->authUrl().'/oauth/token',
                $payload
            );

        logger()->info('Reloadly OAuth', [

            'status' => $response->status(),

            'body' => $response->json(),

        ]);

        if (! $response->successful()) {

            throw new Exception(

                'Reloadly Authentication Failed: '.

                ($response->json('message')
                    ?? $response->body())

            );

        }

        return $response->json('access_token');
    }
}