<?php

namespace App\Services\Reloadly\Sync;

use App\Models\Country;
use App\Services\Reloadly\ReloadlyClient;

class CountrySyncService
{
    public function __construct(
        private readonly ReloadlyClient $client
    ) {}

    public function sync(): int
    {
        $response = $this->client->countries();

        $countries = $response->json();

        $count = 0;

        foreach ($countries as $country) {

            Country::updateOrCreate(

                [
                    "provider_country_id" => $country["id"],
                ],

                [
                    "name" => $country["name"],
                    "iso2" => $country["isoName"],
                    "calling_code" => $country["callingCodes"][0] ?? null,
                    "currency" => $country["currencyCode"] ?? null,
                    "is_active" => true,
                ]

            );

            $count++;
        }

        return $count;
    }
}