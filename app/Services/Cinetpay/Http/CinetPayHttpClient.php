<?php

namespace App\Services\CinetPay\Http;

use App\Services\CinetPay\Config\CinetPayConfig;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class CinetPayHttpClient
{
    public function __construct(
        protected CinetPayConfig $config
    ) {}

    protected function client(): PendingRequest
    {
        return Http::baseUrl(
                $this->config->baseUrl()
            )
            ->acceptJson()
            ->contentType('application/json')
            ->timeout(30)
            ->retry(3, 500);
    }

    public function post(
        string $endpoint,
        array $payload
    ): Response {

        return $this->client()->post(
            $endpoint,
            $payload
        );
    }

    public function get(
        string $endpoint,
        array $query = []
    ): Response {

        return $this->client()->get(
            $endpoint,
            $query
        );
    }
}