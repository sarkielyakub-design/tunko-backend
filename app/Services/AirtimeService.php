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
     * Purchase Airtime through Reloadly.
     */
    public function purchase(array $data): array
    {
        /*
        |--------------------------------------------------------------------------
        | Validate Required Data
        |--------------------------------------------------------------------------
        */

        $countryCode = strtoupper(
            trim($data['country_code'] ?? '')
        );

        $operatorId = (int) (
            $data['operator_id'] ?? 0
        );

        $phone = trim(
            $data['phone'] ?? ''
        );

        $amount = (float) (
            $data['amount'] ?? 0
        );

        $reference = $data['reference'] ?? null;

        /*
        |--------------------------------------------------------------------------
        | Basic Validation
        |--------------------------------------------------------------------------
        */

        if (empty($countryCode)) {
            throw new Exception(
                'Country code is required.'
            );
        }

        if ($operatorId <= 0) {
            throw new Exception(
                'Invalid operator ID.'
            );
        }

        if (empty($phone)) {
            throw new Exception(
                'Recipient phone number is required.'
            );
        }

        if ($amount <= 0) {
            throw new Exception(
                'Invalid airtime amount.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Normalize Nigerian Phone
        |--------------------------------------------------------------------------
        |
        | Example:
        |
        | 08066961807
        |
        | becomes:
        |
        | 8066961807
        |
        */

        if ($countryCode === 'NG') {

            $phone = preg_replace(
                '/\D/',
                '',
                $phone
            );

            if (
                str_starts_with(
                    $phone,
                    '0'
                )
            ) {
                $phone = substr(
                    $phone,
                    1
                );
            }

            if (
                str_starts_with(
                    $phone,
                    '234'
                )
            ) {
                $phone = substr(
                    $phone,
                    3
                );
            }

            if (
                strlen($phone) !== 10
            ) {
                throw new Exception(
                    'Recipient phone number is not valid for Nigeria.'
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Reloadly Purchase
        |--------------------------------------------------------------------------
        */

        $provider = $this->reloadly->purchase([

            'reference' => $reference,

            'country' => $countryCode,

            'operator' => $operatorId,

            /*
            | Airtime does not use a data bundle/product.
            | Reloadly's topup endpoint uses operatorId
            | and amount for airtime.
            */
            'product' => 0,

            'recipient' => $phone,

            'amount' => $amount,

        ]);

        /*
        |--------------------------------------------------------------------------
        | Provider Response
        |--------------------------------------------------------------------------
        */

        return [

            'success' => true,

            'provider_reference' =>
                $provider['transaction_id'] ?? null,

            'transaction_id' =>
                $provider['transaction_id'] ?? null,

            'reference' =>
                $provider['reference'] ?? $reference,

            'status' =>
                strtolower(
                    $provider['status'] ?? 'completed'
                ),

            'amount' =>
                $provider['amount'] ?? $amount,

            'currency' =>
                $provider['currency'] ?? null,

            'message' =>
                'Airtime purchase successful.',

            'raw' =>
                $provider['raw'] ?? null,

        ];
    }
}