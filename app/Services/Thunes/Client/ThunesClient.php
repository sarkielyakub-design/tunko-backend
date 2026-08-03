<?php

namespace App\Services\Thunes\Client;

use Illuminate\Http\Client\Response;
use App\Services\Thunes\Http\ThunesHttpClient;

class ThunesClient
{
    public function __construct(
        private readonly ThunesHttpClient $http
    ) {}

    /**
     * Health Check
     */
    public function health(): Response
    {
        return $this->http->get('/');
    }

    /**
     * Supported Countries
     */
    public function countries(): Response
    {
        return $this->http->get(
            '/countries'
        );
    }
    /**
 * Purchase
 */
public function purchase(
    array $payload
): Response
{
    return $this->http->post(
        "/transactions",
        $payload
    );
}
/**
 * Transaction Status
 */
public function transactionStatus(
    string $transactionId
): Response
{
    return $this->http->get(
        "/transactions/{$transactionId}"
    );
}
}