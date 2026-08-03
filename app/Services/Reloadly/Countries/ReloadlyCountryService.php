<?php

namespace App\Services\Reloadly\Countries;

use App\Services\Reloadly\Client\ReloadlyClient;
use Illuminate\Support\Facades\Cache;
use Exception;

class ReloadlyCountryService
{
    public function __construct(
        protected ReloadlyClient $client
    ) {}

    /**
     * Get all supported countries.
     */
    public function all(): array
    {
        return Cache::remember(
            'reloadly.countries',
            now()->addHours(24),
            function () {

                $response = $this->client->countries();

                if (!$response->successful()) {

                    throw new Exception(
                        'Unable to fetch countries from Reloadly.'
                    );

                }

                return collect(
                    $response->json()
                )->map(function ($country) {

                    return [

                        'id' => $country['isoName'] ?? null,

                        'name' => $country['name'] ?? '',

                        'iso_code' => $country['isoName'] ?? '',

                        'calling_code' => $country['callingCodes'][0] ?? '',

                        'currency' => $country['currencyCode'] ?? '',

                        'flag' => $country['flag'] ?? null,

                    ];

                })->values()->toArray();

            }
        );
    }

    /**
     * Find a country by ISO code.
     */
    public function find(
        string $iso
    ): ?array {

        return collect(
            $this->all()
        )->firstWhere(
            'iso_code',
            strtoupper($iso)
        );

    }
}