<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PaystackService
{
    protected string $secretKey;

    public function __construct()
    {
        $this->secretKey = config('services.paystack.secret');
    }

    /**
     * Initialize Paystack Transaction
     */
    public function initialize(
        string $email,
        float $amount,
        string $reference,
        array $metadata = []
    ) {
        return Http::withToken($this->secretKey)
            ->post(
                'https://api.paystack.co/transaction/initialize',
                [
                    'email' => $email,

                    // Paystack expects Kobo
                    'amount' => intval($amount * 100),

                    'reference' => $reference,

                    'currency' => 'NGN',

                    'metadata' => $metadata,
                ]
            )
            ->json();
    }

    /**
     * Verify Transaction
     */
    public function verify(
        string $reference
    ) {
        return Http::withToken($this->secretKey)
            ->get(
                "https://api.paystack.co/transaction/verify/{$reference}"
            )
            ->json();
    }

    /**
     * List Banks
     */
    public function banks(
        string $country = 'nigeria'
    ) {
        return Http::withToken($this->secretKey)
            ->get(
                'https://api.paystack.co/bank',
                [
                    'country' => $country,
                ]
            )
            ->json();
    }

    /**
     * Resolve Bank Account
     */
    public function resolveAccount(
        string $accountNumber,
        string $bankCode
    ) {
        return Http::withToken($this->secretKey)
            ->get(
                'https://api.paystack.co/bank/resolve',
                [
                    'account_number' => $accountNumber,
                    'bank_code' => $bankCode,
                ]
            )
            ->json();
    }
}