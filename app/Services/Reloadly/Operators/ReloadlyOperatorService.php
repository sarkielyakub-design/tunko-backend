<?php

namespace App\Services\Reloadly\Operators;

use App\Services\Reloadly\Client\ReloadlyClient;
use Illuminate\Support\Facades\Cache;
use Exception;

class ReloadlyOperatorService
{
    public function __construct(
        protected ReloadlyClient $client
    ) {}

    /**
     * Get operators by country.
     */
    public function all(
        string $countryIso
    ): array {

        return Cache::remember(

            "reloadly.operators.$countryIso",

            now()->addHours(24),

            function () use ($countryIso) {

                $response = $this->client->operators(
                    strtoupper($countryIso)
                );

                if (!$response->successful()) {

                    throw new Exception(
                        'Unable to fetch operators.'
                    );

                }

                return collect(
                    $response->json()
                )->map(function ($operator) {

                    return [

                        'id' => $operator['operatorId'],

                        'name' => $operator['name'],

                        'country' =>
                            $operator['country']['isoName'] ?? '',

                        'country_name' =>
                            $operator['country']['name'] ?? '',

                        'logo' =>
                            $operator['logoUrls'][0] ?? null,

                        'bundle' =>
                            $operator['bundle'] ?? false,

                        'data' =>
                            $operator['data'] ?? false,

                        'pin' =>
                            $operator['pin'] ?? false,

                        'supports_local_amount' =>
                            $operator['supportsLocalAmounts'] ?? false,

                        'supports_geographical_recharge_plans' =>
                            $operator['supportsGeographicalRechargePlans'] ?? false,

                    ];

                })->values()->toArray();

            }

        );
    }

    /**
     * Find operator.
     */
    public function find(
        int $operatorId,
        string $countryIso
    ): ?array {

        return collect(

            $this->all($countryIso)

        )->firstWhere(

            'id',

            $operatorId

        );

    }

    /**
     * Auto detect operator.
     */
    public function detect(
        string $phone,
        string $countryIso
    ): array {

        $response = $this->client->detectOperator(

            $phone,

            strtoupper($countryIso)

        );

        if (!$response->successful()) {

            throw new Exception(

                'Unable to detect operator.'

            );

        }

        return $response->json();

    }
}