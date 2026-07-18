<?php

namespace App\Services\Reloadly;

use App\Contracts\Providers\AirtimeProviderInterface;

class ReloadlyAirtimeProvider
    implements AirtimeProviderInterface
{
    public function __construct(
        private readonly ReloadlyClient $client
    ) {}

    public function countries(): array
    {
        return $this->client
            ->countries()
            ->json();
    }

    public function operators(
        int $countryId
    ): array {

        return $this->client
            ->operators($countryId)
            ->json();
    }

    public function detectOperator(
        string $phone,
        string $countryCode
    ): array {

        return $this->client
            ->detectOperator(
                $phone,
                $countryCode
            )
            ->json();
    }

    public function purchase(
        array $payload
    ): array {

        return $this->client
            ->topup($payload)
            ->json();
    }
}