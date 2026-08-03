<?php

namespace App\Services\Reloadly\Products;

use App\Services\Reloadly\Client\ReloadlyClient;
use Illuminate\Support\Facades\Cache;
use Exception;

class ReloadlyProductService
{
    public function __construct(
        protected ReloadlyClient $client
    ) {}

    /**
     * Get products by operator.
     */
    public function all(
        int $operatorId
    ): array {

        return Cache::remember(

            "reloadly.products.$operatorId",

            now()->addHours(12),

            function () use ($operatorId) {

                $response = $this->client->products(
                    $operatorId
                );

                if (! $response->successful()) {

                    throw new Exception(
                        'Unable to fetch products.'
                    );

                }

                return collect(
                    $response->json()
                )->map(function ($product) {

                    return [

                        'id' => $product['productId'] ?? null,

                        'name' => $product['name'] ?? '',

                        'description' =>
                            $product['description'] ?? '',

                        'type' =>
                            $product['type'] ?? '',

                        'amount' =>
                            $product['localFixedAmount'] ?? 0,

                        'currency' =>
                            $product['currencyCode'] ?? '',

                        'min_amount' =>
                            $product['minLocalAmount'] ?? null,

                        'max_amount' =>
                            $product['maxLocalAmount'] ?? null,

                        'validity' =>
                            $product['validity'] ?? null,

                        'bundle' =>
                            $product['bundle'] ?? false,

                        'data' =>
                            $product['data'] ?? false,

                    ];

                })
                ->values()
                ->toArray();

            }

        );
    }

    /**
     * Find product.
     */
    public function find(
        int $operatorId,
        int $productId
    ): ?array {

        return collect(

            $this->all($operatorId)

        )->firstWhere(

            'id',

            $productId

        );

    }
}