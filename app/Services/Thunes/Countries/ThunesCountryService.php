<?php

namespace App\Services\Thunes\Countries;

use Illuminate\Support\Facades\Cache;
use App\Services\Thunes\Client\ThunesClient;

class ThunesCountryService
{
    public function __construct(
        private readonly ThunesClient $client
    ) {}

    /**
     * Retrieve supported countries from Thunes.
     */
    public function all(): array
    {
        return Cache::remember(
            'thunes.countries',
            now()->addDay(),
            function () {

                $response = $this->client->countries();

                if ($response->failed()) {
                    throw new \Exception(
                        'Unable to load countries from Thunes.'
                    );
                }

                return $response->json('data', []);
            }
        );
    }
}