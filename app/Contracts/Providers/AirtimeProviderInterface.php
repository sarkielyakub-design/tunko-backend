<?php

namespace App\Contracts\Providers;

interface AirtimeProviderInterface
{
    public function countries(): array;

    public function operators(
        int $countryId
    ): array;

    public function detectOperator(
        string $phone,
        string $countryCode
    ): array;

    public function purchase(
        array $payload
    ): array;
}