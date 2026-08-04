<?php

namespace App\Services\Reloadly\Quotes;

use App\Services\Reloadly\Client\ReloadlyClient;
use Exception;

class ReloadlyQuoteService
{
    public function __construct(
        protected ReloadlyClient $client
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Get Quote
    |--------------------------------------------------------------------------
    */

    public function quote(array $data): array
    {
        $response = $this->client->quote([

            'operatorId' => (int) $data['operator'],

            'productId' => (int) $data['product'],

            'countryCode' => strtoupper(
                $data['country']
            ),

            'recipientPhone' => [

                'countryCode' => strtoupper(
                    $data['country']
                ),

                'number' => $data['recipient'],

            ],

            'amount' => (float) $data['amount'],

        ]);

        if (! $response->successful()) {

            throw new Exception(

                $response->json('message')
                    ?? 'Unable to retrieve quote.'

            );

        }

        $quote = $response->json();

        return [

            'operator_id' =>
                $quote['operatorId'] ?? null,

            'product_id' =>
                $quote['productId'] ?? null,

            'country' =>
                $quote['countryCode'] ?? null,

            'amount' =>
                (float) ($quote['amount'] ?? 0),

            'currency' =>
                $quote['currencyCode'] ?? '',

            'fee' =>
                (float) ($quote['fee'] ?? 0),

            'discount' =>
                (float) ($quote['discount'] ?? 0),

            'total' =>
                (float) (
                    ($quote['amount'] ?? 0)
                    + ($quote['fee'] ?? 0)
                    - ($quote['discount'] ?? 0)
                ),

            'recipient' =>
                $quote['recipientPhone'] ?? [],

            'raw' => $quote,

        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Validate Quote
    |--------------------------------------------------------------------------
    */

    public function validate(array $quote): bool
    {
        return

            isset($quote['amount']) &&

            $quote['amount'] > 0 &&

            ! empty($quote['currency']);
    }
}