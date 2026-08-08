<?php

namespace App\Services;

use App\Services\Reloadly\Purchase\ReloadlyPurchaseService;
use Exception;

class AirtimeService
{
    public function __construct(
        protected ReloadlyPurchaseService $reloadly
    ) {}

    /**
     * Purchase airtime through Reloadly.
     */
    public function purchase(array $data): array
    {
        /*
        |--------------------------------------------------------------------------
        | Validate Required Data
        |--------------------------------------------------------------------------
        */

        if (empty($data['operator_id'])) {
            throw new Exception(
                'Operator ID is required.'
            );
        }

        if (empty($data['country_code'])) {
            throw new Exception(
                'Country code is required.'
            );
        }

        if (empty($data['phone'])) {
            throw new Exception(
                'Recipient phone number is required.'
            );
        }

        if (
            !isset($data['amount']) ||
            (float) $data['amount'] <= 0
        ) {
            throw new Exception(
                'A valid airtime amount is required.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Generate Provider Purchase Payload
        |--------------------------------------------------------------------------
        */

        $reference =
            $data['reference']
            ?? ('AIR' . strtoupper(
                substr(
                    str_replace(
                        '-',
                        '',
                        (string) \Illuminate\Support\Str::uuid()
                    ),
                    0,
                    12
                )
            ));

        /*
        |--------------------------------------------------------------------------
        | Call Reloadly
        |--------------------------------------------------------------------------
        */

        try {

            $provider = $this->reloadly->purchase([

                'reference' =>
                    $reference,

                'country' =>
                    strtoupper(
                        $data['country_code']
                    ),

                'operator' =>
                    (int) $data['operator_id'],

                /*
                | Airtime does not use a data product ID.
                | Reloadly uses the operator and amount.
                */
                'product' =>
                    $data['product_id']
                    ?? 0,

                'recipient' =>
                    $data['phone'],

                'amount' =>
                    (float) $data['amount'],

            ]);

        } catch (\Throwable $e) {

            throw new Exception(
                $e->getMessage()
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Verify Provider Response
        |--------------------------------------------------------------------------
        */

        if (
            empty(
                $provider['transaction_id']
            )
        ) {

            throw new Exception(
                'Reloadly did not return a transaction reference.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Normalize Response
        |--------------------------------------------------------------------------
        */

        return [

            'success' => true,

            'reference' =>
                $reference,

            'provider_reference' =>
                $provider['transaction_id'],

            'transaction_id' =>
                $provider['transaction_id'],

            'operator_transaction_id' =>
                $provider['operator_transaction_id']
                ?? null,

            'status' =>
                strtolower(
                    $provider['status']
                    ?? 'pending'
                ),

            'amount' =>
                (float) (
                    $provider['amount']
                    ?? $data['amount']
                ),

            'currency' =>
                $provider['currency']
                ?? null,

            'recipient' =>
                $provider['recipient']
                ?? [
                    'phone' =>
                        $data['phone'],
                ],

            'message' =>
                'Airtime purchase successful.',

            'provider_response' =>
                $provider['raw']
                ?? null,

        ];
    }
}