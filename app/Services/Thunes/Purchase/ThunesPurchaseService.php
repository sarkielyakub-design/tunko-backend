<?php

namespace App\Services\Thunes\Purchase;

use App\Services\Thunes\Client\ThunesClient;

class ThunesPurchaseService
{
    public function __construct(
        private readonly ThunesClient $client
    ) {}

    /**
     * Purchase Airtime/Data
     */
    public function purchase(
        array $payload
    ): array {

        $response = $this->client->purchase(
            $payload
        );

        if ($response->failed()) {

            throw new \Exception(

                $response->json('message')
                ?? 'Purchase failed.'

            );

        }

        return $response->json(
            'data',
            []
        );

    }
}