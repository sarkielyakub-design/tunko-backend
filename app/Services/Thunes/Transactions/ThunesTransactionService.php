<?php

namespace App\Services\Thunes\Transaction;

use App\Services\Thunes\Client\ThunesClient;

class ThunesTransactionService
{
    public function __construct(
        private readonly ThunesClient $client
    ) {}

    /**
     * Get transaction status
     */
    public function status(
        string $transactionId
    ): array {

        $response = $this->client->transactionStatus(
            $transactionId
        );

        if ($response->failed()) {

            throw new \Exception(
                'Unable to retrieve transaction.'
            );

        }

        return $response->json(
            'data',
            []
        );

    }
}