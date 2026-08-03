<?php

namespace App\Services\Thunes\Operators;

use Illuminate\Support\Facades\Cache;
use App\Services\Thunes\Client\ThunesClient;

class ThunesOperatorService
{
    public function __construct(
        private readonly ThunesClient $client
    ) {}

    /**
     * Operators by country
     */
    public function all(
        string $countryIso
    ): array {

        return Cache::remember(

            "thunes.operators.$countryIso",

            now()->addHours(12),

            function () use ($countryIso) {

                $response = $this->client->operators(
                    $countryIso
                );

                if ($response->failed()) {
                    throw new \Exception(
                        "Unable to load operators."
                    );
                }

                return $response->json('data', []);

            }

        );
    }
}