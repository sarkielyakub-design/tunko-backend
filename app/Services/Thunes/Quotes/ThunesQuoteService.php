<?php

namespace App\Services\Thunes\Quotes;

use App\Services\Thunes\Client\ThunesClient;

class ThunesQuoteService
{
    public function __construct(
        private readonly ThunesClient $client
    ) {}

    /**
     * Request a live quotation.
     */
    public function quote(array $payload): array
    {
        $response = $this->client->quote(
            $payload
        );

        if ($response->failed()) {

            throw new \Exception(
                $response->json('message')
                ?? 'Unable to retrieve quotation.'
            );

        }

        return $response->json('data', []);
    }
}