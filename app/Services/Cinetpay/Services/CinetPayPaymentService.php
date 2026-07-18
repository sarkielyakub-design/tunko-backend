<?php

namespace App\Services\CinetPay\Services;

use App\Services\CinetPay\Client\CinetPayClient;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class CinetPayPaymentService
{
    public function __construct(
        protected CinetPayClient $client
    ) {}

    /**
     * Initialize wallet deposit
     */
    public function initialize(
        User $user,
        float $amount
    ): array {

        $reference = 'TNK-' . strtoupper(Str::random(16));

        WalletTransaction::create([

            'user_id' => $user->id,

            'reference' => $reference,

            'type' => 'deposit',

            'amount' => $amount,

            'fee' => 0,

            'total' => $amount,

            'currency' => 'XOF',

            'status' => 'pending',

            'payment_gateway' => 'cinetpay',

            'description' => 'Wallet Deposit',

        ]);

        return $this->client->initialize([

            'transaction_id' => $reference,

            'amount' => $amount,

            'customer_name' => $user->first_name,

            'customer_surname' => $user->last_name,

            'customer_email' => $user->email,

            'customer_phone_number' => $user->phone,

            'customer_address' => '',

            'customer_city' => '',

            'customer_country' => '',

            'customer_state' => '',

            'customer_zip_code' => '',

            'description' => 'Tunko Wallet Funding',

        ]);
    }

    /**
     * Verify payment
     */
    public function verify(
        string $reference
    ): array {

        return $this->client->verify(
            $reference
        );
    }

    /**
     * Credit wallet
     */
    public function creditWallet(
        string $reference
    ): WalletTransaction {

        DB::transaction(function () use ($reference) {

            $transaction = WalletTransaction::where(
                'reference',
                $reference
            )->lockForUpdate()->firstOrFail();

            if ($transaction->status === 'success') {
                return;
            }

            $wallet = Wallet::where(
                'user_id',
                $transaction->user_id
            )->lockForUpdate()->firstOrFail();

            $wallet->increment(
                'balance',
                $transaction->amount
            );

            $transaction->update([

                'status' => 'success',

                'completed_at' => now(),

            ]);
        });

        return WalletTransaction::where(
            'reference',
            $reference
        )->firstOrFail();
    }
}