<?php

namespace App\Services\Data;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\DataPurchase;
use App\Services\Reloadly\Purchase\ReloadlyPurchaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Exception;

class DataPurchaseService
{
    public function __construct(
        protected ReloadlyPurchaseService $reloadly
    ) {}

    /**
     * Purchase data bundle.
     */
    public function purchase(
        User $user,
        array $data
    ): array
    {
        $user->load('wallet');

        if (!$user->wallet) {
            throw new Exception(
                'Wallet not found.'
            );
        }

        if (!$user->wallet->is_active) {
            throw new Exception(
                'Wallet is inactive.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Verify Transaction PIN
        |--------------------------------------------------------------------------
        */

        if (
            empty($user->transaction_pin) ||
            !Hash::check(
                $data['pin'],
                $user->transaction_pin
            )
        ) {
            throw new Exception(
                'Invalid transaction PIN.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Verify Wallet Balance
        |--------------------------------------------------------------------------
        */

        if (
            $user->wallet->balance <
            $data['amount']
        ) {
            throw new Exception(
                'Insufficient wallet balance.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Generate Reference
        |--------------------------------------------------------------------------
        */

        $reference =
            'DAT' .
            now()->format('YmdHis') .
            strtoupper(
                Str::random(6)
            );

        return DB::transaction(
            function () use (
                $user,
                $data,
                $reference
            ) {

                $wallet = Wallet::lockForUpdate()
                    ->findOrFail(
                        $user->wallet->id
                    );

                if (
                    $wallet->balance <
                    $data['amount']
                ) {
                    throw new Exception(
                        'Insufficient wallet balance.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Debit Wallet
                |--------------------------------------------------------------------------
                */

                $wallet->balance -=
                    $data['amount'];

                $wallet->save();/*
                |--------------------------------------------------------------------------
                | Call Reloadly
                |--------------------------------------------------------------------------
                */

                try {

                    $provider = $this->reloadly->purchase([

                        'reference' => $reference,

                        'country' => $data['country'],

                        'operator' => $data['operator'],

                        'product' => $data['product'],

                        'recipient' => $data['recipient'],

                        'amount' => $data['amount'],

                    ]);

                } catch (\Throwable $e) {

                    /*
                    |--------------------------------------------------------------------------
                    | Refund Wallet
                    |--------------------------------------------------------------------------
                    */

                    $wallet->balance +=
                        $data['amount'];

                    $wallet->save();

                    throw new Exception(
                        $e->getMessage()
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Save Purchase
                |--------------------------------------------------------------------------
                */

                $purchase = DataPurchase::create([

                    'user_id' => $user->id,

                    'reference' => $reference,

                    'country_id' => $data['country'],

                    'network_id' => $data['operator'],

                    'bundle_id' => $data['product'],

                    'phone' => $data['recipient'],

                    'network' =>
                        $data['network_name'] ?? null,

                    'bundle' =>
                        $data['bundle_name'] ?? null,

                    'amount' => $data['amount'],

                    'currency' =>
                        $provider['currency'],

                    'provider' => 'Reloadly',

                    'provider_reference' =>
                        $provider['transaction_id'],

                    'provider_response' =>
                        json_encode(
                            $provider['raw']
                        ),

                    'status' =>
                        strtolower(
                            $provider['status']
                        ),

                ]);

                /*
                |--------------------------------------------------------------------------
                | Save Transaction
                |--------------------------------------------------------------------------
                */

                Transaction::create([

                    'user_id' => $user->id,

                    'reference' => $reference,

                    'type' => 'data',

                    'amount' => $data['amount'],

                    'fee' => 0,

                    'total' => $data['amount'],

                    'status' =>
                        strtolower(
                            $provider['status']
                        ),

                    'description' =>
                        'Data bundle purchase',

                    'meta' => [

                        'purchase_id' =>
                            $purchase->id,

                        'provider' =>
                            'Reloadly',

                        'provider_reference' =>
                            $provider['transaction_id'],

                    ],

                ]);/*
                |--------------------------------------------------------------------------
                | Refresh Wallet
                |--------------------------------------------------------------------------
                */

                $wallet->refresh();

                /*
                |--------------------------------------------------------------------------
                | Success Response
                |--------------------------------------------------------------------------
                */

                return [

                    'reference' => $reference,

                    'transaction_id' =>
                        $provider['transaction_id'],

                    'provider_reference' =>
                        $provider['transaction_id'],

                    'status' =>
                        strtolower(
                            $provider['status']
                        ),

                    'amount' =>
                        $purchase->amount,

                    'currency' =>
                        $purchase->currency,

                    'recipient' => [

                        'phone' =>
                            $purchase->phone,

                    ],

                    'wallet_balance' =>
                        $wallet->balance,

                    'created_at' =>
                        $purchase->created_at,

                ];

            });

    }
}