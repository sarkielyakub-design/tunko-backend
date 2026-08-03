<?php

namespace App\Services\Reloadly\Countries;

use App\Services\Reloadly\Client\ReloadlyClient;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

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
            now()->addDay(),
            function () {

                $response = $this->client->countries();

                if (! $response->successful()) {
                    throw new RuntimeException(
                        'Failed to fetch countries from Reloadly.'
                    );
                }

                return collect($response->json())
                    ->map(function (array $country) {

                        return [
                            'id'            => $country['isoName'],
                            'name'          => $country['name'],
                            'iso_code'      => $country['isoName'],
                            'calling_code'  => $country['callingCodes'][0] ?? null,
                            'currency'      => $country['currencyCode'],
                            'flag'          => $country['flag'] ?? null,
                        ];

                    })
                    ->values()
                    ->toArray();
            }
        );
    }

    /**
     * Find country by ISO code.
     */
    public function find(string $iso): ?array
    {
        return collect($this->all())
            ->firstWhere('iso_code', strtoupper($iso));
    }

    /**
     * Clear countries cache.
     */
    public function clearCache(): void
    {
        Cache::forget('reloadly.countries');
    }
}