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

    /*
    |--------------------------------------------------------------------------
    | Get Products
    |--------------------------------------------------------------------------
    */

    public function all(
        int $operatorId
    ): array {

        return Cache::remember(
            "reloadly.products.{$operatorId}",
            now()->addDay(),
            fn () => $this->fetchProducts($operatorId)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Find Product
    |--------------------------------------------------------------------------
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

    /*
    |--------------------------------------------------------------------------
    | Refresh Cache
    |--------------------------------------------------------------------------
    */

    public function refresh(
        int $operatorId
    ): array {

        Cache::forget(
            "reloadly.products.{$operatorId}"
        );

        return $this->all($operatorId);
    }

    /*
    |--------------------------------------------------------------------------
    | Fetch Products
    |--------------------------------------------------------------------------
    */

    protected function fetchProducts(
        int $operatorId
    ): array {

        $response = $this->client->products(
            $operatorId
        );

        if (! $response->successful()) {

            throw new Exception(

                $response->json('message')
                ?? 'Unable to fetch products.'

            );

        }

        $operator = $response->json();

        $products = $operator['fixedAmountsDescriptions'] ?? [];

        $amounts = $operator['fixedAmounts'] ?? [];

        return collect($amounts)

            ->map(function ($amount, $index) use ($products, $operator) {

                return [

                    'id' => $index + 1,

                    'operator_id' =>
                        $operator['operatorId'],

                    'name' =>
                        $products[$index]['en']
                        ?? 'Data Bundle',

                    'volume' =>
                        $products[$index]['en']
                        ?? '',

                    'amount' =>
                        (float) $amount,

                    'currency' =>
                        $operator['fx']['currencyCode']
                        ?? '',

                    'validity' =>
                        $this->extractValidity(
                            $products[$index]['en']
                            ?? ''
                        ),

                    'raw' => [

                        'description' =>
                            $products[$index] ?? [],

                        'amount' => $amount,

                    ],

                ];

            })

            ->sortBy('amount')

            ->values()

            ->toArray();
    }

    /*
    |--------------------------------------------------------------------------
    | Extract Validity
    |--------------------------------------------------------------------------
    */

    protected function extractValidity(
        string $text
    ): string {

        preg_match(
            '/(\d+\s*(day|days|week|weeks|month|months))/i',
            $text,
            $matches
        );

        return $matches[1] ?? '';
    }
}