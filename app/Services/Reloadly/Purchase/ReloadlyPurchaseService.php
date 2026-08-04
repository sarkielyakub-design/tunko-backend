<?php

namespace App\Services\Reloadly\Purchase;

use App\Services\Reloadly\Client\ReloadlyClient;
use Exception;

class ReloadlyPurchaseService
{
    public function __construct(
        protected ReloadlyClient $client
    ) {}

    /*
    |--------------------------------------------------------------------------
    | Purchase Airtime / Data
    |--------------------------------------------------------------------------
    */

    public function purchase(
        array $data
    ): array {

        $payload = [

            'operatorId' => (int) $data['operator'],

            'productId' => (int) $data['product'],

            'recipientPhone' => [

                'countryCode' => strtoupper(
                    $data['country']
                ),

                'number' => $data['recipient'],

            ],

            'amount' => (float) $data['amount'],

            'customIdentifier' =>
                $data['reference'] ?? null,

        ];

        $response = $this->client->purchase(
            $payload
        );

        if (! $response->successful()) {

            throw new Exception(

                $response->json('message')

                ?? 'Reloadly purchase failed.'

            );

        }

        return $this->transform(
            $response->json()
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Check Transaction Status
    |--------------------------------------------------------------------------
    */

    public function status(
        string $transactionId
    ): array {

        $response = $this->client->get(
            "/topups/{$transactionId}"
        );

        if (! $response->successful()) {

            throw new Exception(

                $response->json('message')

                ?? 'Unable to retrieve transaction.'

            );

        }

        return $this->transform(
            $response->json()
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Transform Provider Response
    |--------------------------------------------------------------------------
    */

    protected function transform(
        array $purchase
    ): array {

        return [

            'transaction_id' =>
                $purchase['transactionId'] ?? null,

            'reference' =>
                $purchase['customIdentifier'] ?? null,

            'status' =>
                strtoupper(
                    $purchase['status'] ?? 'PENDING'
                ),

            'operator_transaction_id' =>
                $purchase['operatorTransactionId'] ?? null,

            'recipient' =>
                $purchase['recipientPhone'] ?? [],

            'amount' =>
                (float) ($purchase['amount'] ?? 0),

            'currency' =>
                $purchase['currencyCode'] ?? '',

            'balance' =>
                (float) ($purchase['balanceInfo']['oldBalance'] ?? 0),

            'new_balance' =>
                (float) ($purchase['balanceInfo']['newBalance'] ?? 0),

            'discount' =>
                (float) ($purchase['discount'] ?? 0),

            'commission' =>
                (float) ($purchase['commission'] ?? 0),

            'raw' => $purchase,

        ];
    }
}