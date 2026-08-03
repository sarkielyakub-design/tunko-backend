<?php

namespace App\Services\Reloadly\Auth;

use App\Services\Reloadly\Support\ReloadlyConfig;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Exception;

class ReloadlyAuthService
{
    protected const CACHE_KEY = 'reloadly_access_token';

    public function __construct(
        protected ReloadlyConfig $config
    ) {}

    /**
     * Get valid access token.
     */
    public function token(): string
    {
        return Cache::remember(
            self::CACHE_KEY,
            now()->addMinutes(55),
            function () {
                return $this->requestToken();
            }
        );
    }

    /**
     * Request new OAuth token.
     */
  protected function requestToken(): string
{
    $response = Http::timeout(
        $this->config->timeout()
    )->post(
        $this->config->authUrl() . '/oauth/token',
        [
            'client_id' => $this->config->clientId(),
            'client_secret' => $this->config->clientSecret(),
            'grant_type' => 'client_credentials',
           'audience' => $this->config->audience(),
        ]
    );

    if (!$response->successful()) {

        throw new Exception(
            "Reloadly Authentication Failed\n".
            "HTTP Status: ".$response->status()."\n".
            "Response: ".$response->body()
        );

    }

    return $response->json('access_token');
}
    /**
     * Authorization headers.
     */
    public function headers(): array
    {
        return [

            'Authorization' => 'Bearer ' . $this->token(),

            'Accept' => 'application/json',

            'Content-Type' => 'application/json',

        ];
    }

    /**
     * Clear cached token.
     */
    public function refresh(): void
    {
        Cache::forget(
            self::CACHE_KEY
        );
    }
}