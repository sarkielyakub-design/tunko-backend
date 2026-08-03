<?php

namespace App\Services\Reloadly\Purchase;

use App\Services\Reloadly\Client\ReloadlyClient;
use Exception;

class ReloadlyPurchaseService
{
    public function __construct(
        protected ReloadlyClient $client
    ) {}

    /**
     * Purchase Airtime/Data
     */
    public function purchase(array $data): array
    {
        $response = $this->client->purchase([

            'operatorId' => $data['operator'],

            'productId' => $data['product'],

            'recipientPhone' => [

                'countryCode' => strtoupper(
                    $data['country']
                ),

                'number' => $data['recipient'],

            ],

            'amount' => $data['amount'],

            'customIdentifier' =>
                $data['reference'] ?? null,

        ]);

        if (! $response->successful()) {

            throw new Exception(

                $response->json('message')
                    ?? 'Purchase failed.'

            );

        }

        $purchase = $response->json();

        return [

            'transaction_id' =>
                $purchase['transactionId'] ?? null,

            'reference' =>
                $purchase['customIdentifier']
                    ?? null,

            'status' =>
                $purchase['status']
                    ?? 'PENDING',

            'operator_transaction_id' =>
                $purchase['operatorTransactionId']
                    ?? null,

            'recipient' =>
                $purchase['recipientPhone']
                    ?? null,

            'amount' =>
                $purchase['amount']
                    ?? 0,

            'currency' =>
                $purchase['currencyCode']
                    ?? '',

            'raw' => $purchase,

        ];
    }
}