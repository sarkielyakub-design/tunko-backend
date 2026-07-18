<?php

namespace App\Services\Reloadly;

use RuntimeException;

class AirtimeService
{
    public function __construct(
        private readonly ReloadlyClient $client
    ) {
    }

    /**
     * Purchase Airtime
     */
    public function purchase(array $payload): array
    {
        $response = $this->client->topup([
            "operatorId" => $payload["operator_id"],
            "amount" => $payload["amount"],
            "useLocalAmount" => true,
            "recipientPhone" => [
                "countryCode" => $payload["country_code"],
                "number" => $payload["phone"],
            ],
            "customIdentifier" => $payload["reference"],
        ]);

        if ($response->failed()) {
            throw new RuntimeException(
                $response->json("message")
                    ?? "Reloadly purchase failed."
            );
        }

        return [
            "provider" => "reloadly",
            "provider_reference" => $response->json("transactionId"),
            "status" => strtolower(
                $response->json("status", "processing")
            ),
            "raw_response" => $response->json(),
        ];
    }
}