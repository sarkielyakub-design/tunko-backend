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

    /*
    |--------------------------------------------------------------------------
    | Get Operators By Country
    |--------------------------------------------------------------------------
    */

    public function all(
        string $countryIso
    ): array {

        $countryIso = strtoupper($countryIso);

        return Cache::remember(
            "reloadly.operators.{$countryIso}",
            now()->addDay(),
            fn () => $this->fetchOperators($countryIso)
        );
    }
public function airtime(string $countryIso): array
{
    $countryIso = strtoupper($countryIso);

    return Cache::remember(
        "reloadly.airtime.{$countryIso}",
        now()->addDay(),
        fn () => $this->fetchAirtimeOperators($countryIso)
    );
}
protected function fetchAirtimeOperators(
    string $countryIso
): array {

    $response = $this->client->operators(
        $countryIso
    );

    if (! $response->successful()) {

        throw new Exception(
            $response->json('message')
            ?? 'Unable to fetch operators.'
        );

    }

    return collect($response->json())

        ->map(function ($operator) {

            return [

                'id' => (int) ($operator['operatorId'] ?? 0),

                'country_id' =>
                    $operator['country']['isoName'] ?? '',

                'name' =>
                    $operator['name'] ?? '',

                'code' =>
                    $operator['bundle'] ?? '',

                'logo' =>
                    $operator['logoUrls'][0] ?? '',

                'supports_data' =>
                    (bool) ($operator['data'] ?? false),

                'supports_pin' =>
                    (bool) ($operator['pin'] ?? false),

                'supports_local_amount' =>
                    (bool) ($operator['supportsLocalAmounts'] ?? false),

                'supports_geographical_recharge_plans' =>
                    (bool) ($operator['supportsGeographicalRechargePlans'] ?? false),

                'fx_rate' =>
                    (double) ($operator['fx']['rate'] ?? 0),

                'currency' =>
                    $operator['fx']['currencyCode'] ?? '',

            ];

        })

        ->sortBy('name')

        ->values()

        ->toArray();
}
    /*
    |--------------------------------------------------------------------------
    | Find Operator
    |--------------------------------------------------------------------------
    */

    public function find(
        string $countryIso,
        int $operatorId
    ): ?array {

        return collect(
            $this->all($countryIso)
        )->firstWhere(
            'id',
            $operatorId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Refresh Cache
    |--------------------------------------------------------------------------
    */

    public function refresh(
        string $countryIso
    ): array {

        $countryIso = strtoupper($countryIso);

        Cache::forget(
            "reloadly.operators.{$countryIso}"
        );

        return $this->all($countryIso);
    }

    /*
    |--------------------------------------------------------------------------
    | Fetch Operators
    |--------------------------------------------------------------------------
    */

    protected function fetchOperators(
        string $countryIso
    ): array {

        $response = $this->client->operators(
            $countryIso
        );

        if (! $response->successful()) {

            throw new Exception(

                $response->json('message')

                ?? 'Unable to fetch operators.'

            );

        }

        return collect(
            $response->json()
        )

        ->filter(fn ($operator) =>

            $operator['data'] ?? false

        )

        ->map(function ($operator) {

            return [

                'id' => (int) ($operator['operatorId'] ?? 0),

                'country_id' =>
                    $operator['country']['isoName']
                    ?? '',

                'name' =>
                    $operator['name']
                    ?? '',

                'code' =>
                    $operator['bundle']
                    ?? '',

                'logo' =>
                    $operator['logoUrls'][0]
                    ?? '',

                'supports_data' =>
                    (bool) ($operator['data'] ?? false),

                'supports_pin' =>
                    (bool) ($operator['pin'] ?? false),

                'supports_local_amount' =>
                    (bool) ($operator['supportsLocalAmounts'] ?? false),

                'supports_geographical_recharge_plans' =>
                    (bool) ($operator['supportsGeographicalRechargePlans'] ?? false),

                'fx_rate' =>
                    (double) ($operator['fx']['rate'] ?? 0),

                'currency' =>
                    $operator['fx']['currencyCode']
                    ?? '',

            ];

        })

        ->sortBy('name')

        ->values()

        ->toArray();
    }
}