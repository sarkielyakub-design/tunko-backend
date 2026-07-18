<?php

namespace App\Services\Reloadly;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class ReloadlyHttpClient
{
    public function __construct(
        private readonly ReloadlyAuthService $auth,
        private readonly ReloadlyConfig $config
    ) {
    }

    public function get(string $endpoint, array $query = []): Response
    {
        return $this->request()
            ->get($endpoint, $query);
    }

    public function post(string $endpoint, array $data = []): Response
    {
        return $this->request()
            ->post($endpoint, $data);
    }

    public function put(string $endpoint, array $data = []): Response
    {
        return $this->request()
            ->put($endpoint, $data);
    }

    public function delete(string $endpoint): Response
    {
        return $this->request()
            ->delete($endpoint);
    }

    private function request()
    {
        return Http::baseUrl(
                rtrim($this->config->topupUrl(), '/')
            )
            ->acceptJson()
            ->withToken(
                $this->auth->accessToken()
            )
            ->timeout(
                $this->config->timeout()
            )
            ->retry(
                3,
                1000
            );
    }
}