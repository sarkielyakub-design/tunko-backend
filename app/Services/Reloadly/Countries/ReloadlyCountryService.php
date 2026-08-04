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
    $westAfrica = [
        'BJ', // Benin
        'BF', // Burkina Faso
        'CV', // Cape Verde
        'CI', // Côte d'Ivoire
        'GM', // Gambia
        'GH', // Ghana
        'GN', // Guinea
        'GW', // Guinea-Bissau
        'LR', // Liberia
        'ML', // Mali
        'MR', // Mauritania
        'NE', // Niger
        'NG', // Nigeria
        'SN', // Senegal
        'SL', // Sierra Leone
        'TG', // Togo
    ];

    $countries = [];

    $reloadlyCountries = collect(
        $this->client->countries()->json()
    );

    foreach ($westAfrica as $iso) {

        $operators = $this->client
            ->operators($iso)
            ->json();

        $supportsData = collect($operators)
            ->contains(fn ($operator) => ($operator['data'] ?? false) === true);

        if (! $supportsData) {
            continue;
        }

        $country = $reloadlyCountries
            ->firstWhere('isoName', $iso);

        if (! $country) {
            continue;
        }

        $countries[] = [
            'id' => $country['isoName'],
            'name' => $country['name'],
            'iso_code' => $country['isoName'],
            'calling_code' => $country['callingCodes'][0] ?? '',
            'currency' => $country['currencyCode'],

            // PNG flag instead of SVG
            'flag' => 'https://flagcdn.com/w80/' .
                strtolower($country['isoName']) .
                '.png',
        ];
    }

    return collect($countries)
        ->sortBy('name')
        ->values()
        ->toArray();
}
}