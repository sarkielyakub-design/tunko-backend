<?php

namespace App\Services\Reloadly\Countries;

use App\Services\Reloadly\Client\ReloadlyClient;
use Illuminate\Support\Facades\Cache;
use Exception;

class ReloadlyCountryService
{
    private const CACHE_KEY = 'reloadly.countries';

    public function __construct(
        protected ReloadlyClient $client
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Get All Countries
    |--------------------------------------------------------------------------
    */

    public function all(): array
    {
        return Cache::remember(
            self::CACHE_KEY,
            now()->addDay(),
            fn () => $this->fetchCountries()
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Find Country
    |--------------------------------------------------------------------------
    */

    public function find(
        string $isoCode
    ): ?array {

        return collect($this->all())
            ->firstWhere(
                'iso_code',
                strtoupper($isoCode)
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Refresh Cache
    |--------------------------------------------------------------------------
    */

    public function refresh(): array
    {
        Cache::forget(self::CACHE_KEY);

        return $this->all();
    }

    /*
    |--------------------------------------------------------------------------
    | Fetch Countries From Reloadly
    |--------------------------------------------------------------------------
    */

    protected function fetchCountries(): array
    {
        $response = $this->client->countries();

        if (! $response->successful()) {

            throw new Exception(
                $response->json('message')
                ?? 'Unable to fetch Reloadly countries.'
            );
        }

        return collect($response->json())
            ->map(function ($country) {

                return [

                    'id' => $country['isoName'] ?? '',

                    'name' => $country['name'] ?? '',

                    'iso_code' => $country['isoName'] ?? '',

                    'calling_code' =>
                        $country['callingCodes'][0] ?? '',

                    'currency' =>
                        $country['currencyCode'] ?? '',

                    'flag' =>
                        $country['flag'] ?? '',

                ];

            })
            ->sortBy('name')
            ->values()
            ->toArray();
    }
}