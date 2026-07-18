<?php

namespace App\Services\Reloadly;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ReloadlyAuthService
{
    private const CACHE_KEY = 'reloadly_access_token';

    public function __construct(
        private readonly ReloadlyConfig $config
    ) {
    }

    /**
     * Get a valid OAuth access token.
     */
    public function accessToken(): string
    {
        return Cache::remember(
            self::CACHE_KEY,
            now()->addMinutes(55),
            fn () => $this->authenticate()
        );
    }

    /**
     * Force refresh the token.
     */
    public function refreshToken(): string
    {
        Cache::forget(self::CACHE_KEY);

        return $this->accessToken();
    }

    /**
     * Authenticate with Reloadly.
     */
    private function authenticate(): string
    {
        $response = Http::timeout(
            $this->config->timeout()
        )->asForm()->post(
            rtrim($this->config->authUrl(), '/') . '/oauth/token',
            [
                'client_id' => $this->config->clientId(),
                'client_secret' => $this->config->clientSecret(),
                'grant_type' => 'client_credentials',
                'audience' => $this->config->audience(),
            ]
        );

        if ($response->failed()) {
            throw new RuntimeException(
                'Reloadly authentication failed: ' .
                $response->body()
            );
        }

        $token = $response->json('access_token');

        if (!$token) {
            throw new RuntimeException(
                'Reloadly did not return an access token.'
            );
        }

        return $token;
    }
}