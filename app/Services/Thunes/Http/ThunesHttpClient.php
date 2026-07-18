<?php

namespace App\Services\Thunes\Http;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use App\Services\Thunes\Support\ThunesConfig;
use App\Services\Thunes\Auth\ThunesAuthService;

class ThunesHttpClient
{
    public function __construct(
        private readonly ThunesConfig $config,
        private readonly ThunesAuthService $auth
    ) {}

    protected function client()
    {
        return Http::baseUrl($this->config->baseUrl())

            ->timeout($this->config->timeout())

            ->retry(
                $this->config->retry(),
                1000
            )

            ->withHeaders(
                $this->auth->headers()
            );
    }

    public function get(
        string $endpoint,
        array $query = []
    ): Response
    {
        return $this->client()
            ->get($endpoint, $query);
    }

    public function post(
        string $endpoint,
        array $payload = []
    ): Response
    {
        return $this->client()
            ->post($endpoint, $payload);
    }

    public function put(
        string $endpoint,
        array $payload = []
    ): Response
    {
        return $this->client()
            ->put($endpoint, $payload);
    }

    public function delete(
        string $endpoint
    ): Response
    {
        return $this->client()
            ->delete($endpoint);
    }
}