<?php

namespace App\Services\Reloadly\Quotes;

use App\Services\Reloadly\Client\ReloadlyClient;
use Exception;

class ReloadlyQuoteService
{
    public function __construct(
        protected ReloadlyClient $client
    ) {}

    /**
     * Generate quote.
     */
    public function quote(array $data): array
    {
        $response = $this->client->quote([

            'operatorId' => $data['operator'],

            'productId' => $data['product'],

            'recipientPhone' => [

                'countryCode' => strtoupper(
                    $data['country']
                ),

                'number' => $data['recipient'],

            ],

            'amount' => $data['amount'],

        ]);

        if (! $response->successful()) {

            throw new Exception(

                $response->json('message')
                    ?? 'Unable to generate quote.'

            );

        }

        $quote = $response->json();

        return [

            'provider_quote_id' =>
                $quote['quoteId'] ?? null,

            'amount' =>
                $quote['amount'] ?? 0,

            'fee' =>
                $quote['fee'] ?? 0,

            'discount' =>
                $quote['discount'] ?? 0,

            'tax' =>
                $quote['tax'] ?? 0,

            'total' =>
                $quote['totalAmount']
                    ?? $quote['amount'],

            'currency' =>
                $quote['currencyCode'] ?? '',

            'expires_at' =>
                $quote['expiresAt'] ?? null,

        ];
    }
}