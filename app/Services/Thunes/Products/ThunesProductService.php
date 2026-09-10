<?php

namespace App\Services\Thunes\Products;

use Illuminate\Support\Facades\Cache;
use App\Services\Thunes\Client\ThunesClient;

class ThunesProductService
{
    public function __construct(
        private readonly ThunesClient $client
    ) {}

    /**
     * Products/Bundles for an operator.
     */
    public function all(
        int $operatorId
    ): array {

        return Cache::remember(

            "thunes.products.$operatorId",

            now()->addHours(6),

            function () use ($operatorId) {

                $response = $this->client->products(
                    $operatorId
                );

                if ($response->failed()) {

                    throw new \Exception(
                        "Unable to load products from Thunes."
                    );

                }

                return $response->json(
                    'data',
                    []
                );

            }

        );

    }
}