<?php

namespace App\Services\Voucher;

use App\Models\User;
use App\Models\Voucher;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Exception;

class VoucherService
{
    /**
     * Purchase an available voucher.
     *
     * This operation is atomic:
     *
     * 1. Verify user wallet
     * 2. Verify transaction PIN
     * 3. Lock wallet
     * 4. Lock one available voucher
     * 5. Debit wallet
     * 6. Assign voucher to user
     * 7. Create transaction
     * 8. Return voucher information
     */
    public function purchase(
        User $user,
        array $data
    ): array {

        return DB::transaction(function () use (
            $user,
            $data
        ) {

            /*
            |--------------------------------------------------------------------------
            | Load Wallet
            |--------------------------------------------------------------------------
            */

            $wallet = Wallet::lockForUpdate()
                ->where(
                    'user_id',
                    $user->id
                )
                ->first();

            if (!$wallet) {
                throw new Exception(
                    'Wallet not found.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Wallet Status
            |--------------------------------------------------------------------------
            */

            if (!$wallet->is_active) {
                throw new Exception(
                    'Wallet is inactive.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Transaction PIN
            |--------------------------------------------------------------------------
            */

            if (
                empty($user->transaction_pin) ||
                empty($data['pin']) ||
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
            | Find Voucher
            |--------------------------------------------------------------------------
            |
            | lockForUpdate() prevents two customers from
            | receiving the same voucher.
            |
            */

            $query = Voucher::query()
                ->where(
                    'status',
                    'available'
                )
                ->where(
                    'type',
                    $data['type']
                )
                ->where(
                    'country_code',
                    strtoupper(
                        $data['country_code']
                    )
                );

            /*
            |--------------------------------------------------------------------------
            | Network Filter
            |--------------------------------------------------------------------------
            */

            if (
                !empty(
                    $data['network_id']
                )
            ) {
                $query->where(
                    'network_id',
                    $data['network_id']
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Amount Filter
            |--------------------------------------------------------------------------
            */

            if (
                isset($data['amount'])
            ) {
                $query->where(
                    'amount',
                    $data['amount']
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Lock Voucher
            |--------------------------------------------------------------------------
            */

            $voucher = $query
                ->orderBy('id')
                ->lockForUpdate()
                ->first();

            if (!$voucher) {
                throw new Exception(
                    'This voucher is currently unavailable.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Purchase Amount
            |--------------------------------------------------------------------------
            */

            $amount = (float) $voucher->amount;

            /*
            |--------------------------------------------------------------------------
            | Currency
            |--------------------------------------------------------------------------
            */

            $currency =
                $voucher->currency
                ?: $wallet->currency;

            /*
            |--------------------------------------------------------------------------
            | Balance
            |--------------------------------------------------------------------------
            */

            if (
                (float) $wallet->balance
                < $amount
            ) {
                throw new Exception(
                    'Insufficient wallet balance.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Purchase Reference
            |--------------------------------------------------------------------------
            */

            $reference =
                'VCH' .
                now()->format(
                    'YmdHis'
                ) .
                strtoupper(
                    Str::random(6)
                );

            /*
            |--------------------------------------------------------------------------
            | Debit Wallet
            |--------------------------------------------------------------------------
            */

            $wallet->balance =
                (float) $wallet->balance
                - $amount;

            $wallet->save();

            /*
            |--------------------------------------------------------------------------
            | Mark Voucher As Sold
            |--------------------------------------------------------------------------
            */

            $voucher->update([

                'status' =>
                    'sold',

                'user_id' =>
                    $user->id,

                'purchase_reference' =>
                    $reference,

                'sold_at' =>
                    now(),

            ]);

            /*
            |--------------------------------------------------------------------------
            | Create Transaction
            |--------------------------------------------------------------------------
            */

            $transaction =
                Transaction::create([

                    'user_id' =>
                        $user->id,

                    'reference' =>
                        $reference,

                    'type' =>
                        $data['type'] === 'airtime'
                            ? 'airtime'
                            : 'data',

                    'title' =>
                        $voucher->product_name
                            ?: ucfirst(
                                $data['type']
                            ) .
                            ' Voucher',

                    'description' =>
                        'Voucher purchase',

                    'amount' =>
                        $amount,

                    'currency' =>
                        $currency,

                    'fee' =>
                        0,

                    'total' =>
                        $amount,

                    'status' =>
                        'completed',

                    'meta' => [

                        'voucher_id' =>
                            $voucher->id,

                        'voucher_reference' =>
                            $voucher->reference,

                        'type' =>
                            $voucher->type,

                        'country_code' =>
                            $voucher->country_code,

                        'network_id' =>
                            $voucher->network_id,

                        'network_name' =>
                            $voucher->network_name,

                        'product_name' =>
                            $voucher->product_name,

                    ],

                ]);

            /*
            |--------------------------------------------------------------------------
            | Refresh Wallet
            |--------------------------------------------------------------------------
            */

            $wallet->refresh();

            /*
            |--------------------------------------------------------------------------
            | Return Purchase
            |--------------------------------------------------------------------------
            */

            return [

                'success' =>
                    true,

                'reference' =>
                    $reference,

                'voucher_reference' =>
                    $voucher->reference,

                'type' =>
                    $voucher->type,

                'country_code' =>
                    $voucher->country_code,

                'network' =>
                    $voucher->network_name,

                'product' =>
                    $voucher->product_name,

                'amount' =>
                    (float) $voucher->amount,

                'currency' =>
                    $currency,

                /*
                | IMPORTANT:
                | This is returned only after successful
                | purchase.
                */

                'pin' =>
                    $voucher->pin,

                'status' =>
                    'completed',

                'wallet_balance' =>
                    (float) $wallet->balance,

                'transaction_reference' =>
                    $transaction->reference,

                'sold_at' =>
                    $voucher->sold_at,

            ];
        });
    }
}
