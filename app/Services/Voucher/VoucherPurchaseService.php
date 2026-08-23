<?php

namespace App\Services\Voucher;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Exception;

class VoucherPurchaseService
{
    /**
     * Purchase one physical voucher.
     *
     * Flow:
     *
     * Customer enters transaction PIN
     *          ↓
     * Verify transaction PIN
     *          ↓
     * Lock customer wallet
     *          ↓
     * Find available company voucher
     *          ↓
     * Check wallet balance
     *          ↓
     * Debit wallet
     *          ↓
     * Mark voucher as SOLD
     *          ↓
     * Create transaction
     *          ↓
     * Return voucher PIN
     */
    public function purchase(
        User $user,
        array $data
    ): array {

        return DB::transaction(
            function () use (
                $user,
                $data
            ) {

                /*
                |--------------------------------------------------------------------------
                | Normalize Input
                |--------------------------------------------------------------------------
                */

                $countryCode = strtoupper(
                    trim(
                        $data['country_code'] ?? ''
                    )
                );

                $type = strtolower(
                    trim(
                        $data['type'] ?? ''
                    )
                );

                $amount = (float) (
                    $data['amount'] ?? 0
                );

                $networkId =
                    $data['network_id'] ?? null;

                $productName =
                    !empty(
                        $data['product_name'] ?? null
                    )
                        ? trim(
                            $data['product_name']
                        )
                        : null;

                /*
                |--------------------------------------------------------------------------
                | Validate Country
                |--------------------------------------------------------------------------
                |
                | NE = Niger
                | TD = Chad
                |
                | IMPORTANT:
                | This is Niger and Chad.
                | Nigeria is NG and is NOT included.
                |
                */

                if (
                    !in_array(
                        $countryCode,
                        [
                            'NE',
                            'TD',
                        ],
                        true
                    )
                ) {
                    throw new Exception(
                        'Voucher purchases are currently available only for Niger and Chad.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Validate Voucher Type
                |--------------------------------------------------------------------------
                */

                if (
                    !in_array(
                        $type,
                        [
                            'airtime',
                            'data',
                        ],
                        true
                    )
                ) {
                    throw new Exception(
                        'Invalid voucher type.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Validate Amount
                |--------------------------------------------------------------------------
                */

                if ($amount <= 0) {
                    throw new Exception(
                        'Invalid voucher amount.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Verify Transaction PIN
                |--------------------------------------------------------------------------
                */

                $transactionPin =
                    $data['pin'] ?? null;

                if (
                    empty(
                        $user->transaction_pin
                    )
                ) {
                    throw new Exception(
                        'Transaction PIN has not been created.'
                    );
                }

                if (
                    empty($transactionPin)
                    ||
                    !Hash::check(
                        $transactionPin,
                        $user->transaction_pin
                    )
                ) {
                    throw new Exception(
                        'Invalid transaction PIN.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Load + Lock Wallet
                |--------------------------------------------------------------------------
                |
                | Locking prevents two simultaneous purchases
                | from spending the same balance.
                |
                */

                $wallet = $user->wallet()
                    ->lockForUpdate()
                    ->first();

                if (!$wallet) {
                    throw new Exception(
                        'Wallet not found.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Wallet Active
                |--------------------------------------------------------------------------
                */

                if (
                    !$wallet->is_active
                ) {
                    throw new Exception(
                        'Wallet is inactive.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Find Available Voucher
                |--------------------------------------------------------------------------
                */

                $query = Voucher::query()

                    ->where(
                        'status',
                        'available'
                    )

                    ->where(
                        'type',
                        $type
                    )

                    ->where(
                        'country_code',
                        $countryCode
                    );

                /*
                |--------------------------------------------------------------------------
                | Network Filter
                |--------------------------------------------------------------------------
                */

                if (
                    !empty($networkId)
                ) {
                    $query->where(
                        'network_id',
                        $networkId
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Product Filter
                |--------------------------------------------------------------------------
                */

                if (
                    !empty($productName)
                ) {
                    $query->where(
                        'product_name',
                        $productName
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Amount
                |--------------------------------------------------------------------------
                */

                $query->where(
                    'amount',
                    $amount
                );

                /*
                |--------------------------------------------------------------------------
                | Expiry
                |--------------------------------------------------------------------------
                */

                $query->where(
                    function ($q) {

                        $q->whereNull(
                            'expires_at'
                        )

                        ->orWhere(
                            'expires_at',
                            '>',
                            now()
                        );

                    }
                );

                /*
                |--------------------------------------------------------------------------
                | Lock ONE Voucher
                |--------------------------------------------------------------------------
                |
                | Important:
                | The voucher row is locked before it is sold.
                |
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
                | Verify Voucher Amount
                |--------------------------------------------------------------------------
                */

                $voucherAmount =
                    (float) $voucher->amount;

                if (
                    $voucherAmount <= 0
                ) {
                    throw new Exception(
                        'Invalid voucher amount.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Check Wallet Balance
                |--------------------------------------------------------------------------
                */

                $walletBalance =
                    (float) $wallet->balance;

                if (
                    $walletBalance
                    < $voucherAmount
                ) {
                    throw new Exception(
                        'Insufficient wallet balance.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Generate Purchase Reference
                |--------------------------------------------------------------------------
                */

                $reference =
                    $this->generateReference();

                /*
                |--------------------------------------------------------------------------
                | Debit Wallet
                |--------------------------------------------------------------------------
                */

                $wallet->balance =
                    $walletBalance
                    - $voucherAmount;

                $wallet->save();

                /*
                |--------------------------------------------------------------------------
                | Mark Voucher SOLD
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

                Transaction::create([

                    'user_id' =>
                        $user->id,

                    'reference' =>
                        $reference,

                    'type' =>
                        'voucher',

                    'amount' =>
                        $voucherAmount,

                    'fee' =>
                        0,

                    'total' =>
                        $voucherAmount,

                    'status' =>
                        'completed',

                    'description' =>
                        ucfirst($type)
                        .
                        ' voucher purchase',

                    'meta' => [

                        'voucher_id' =>
                            $voucher->id,

                        'voucher_reference' =>
                            $voucher->reference,

                        'voucher_type' =>
                            $voucher->type,

                        'country_code' =>
                            $voucher->country_code,

                        'network_id' =>
                            $voucher->network_id,

                        'network_name' =>
                            $voucher->network_name,

                        'product_name' =>
                            $voucher->product_name,

                        'purchase_method' =>
                            'physical_company_voucher',

                        'provider' =>
                            $voucher->provider,

                        'provider_reference' =>
                            $voucher->provider_reference,

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
                | Return Successful Purchase
                |--------------------------------------------------------------------------
                |
                | The physical voucher PIN is returned only after:
                |
                | 1. Wallet was verified
                | 2. Transaction PIN was verified
                | 3. Voucher was locked
                | 4. Wallet was debited
                | 5. Voucher was marked SOLD
                | 6. Transaction was created
                |
                */

                return [

                    'reference' =>
                        $reference,

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

                    'amount' =>
                        $voucherAmount,

                    'currency' =>
                        $voucher->currency,

                    /*
                    |--------------------------------------------------------------------------
                    | PHYSICAL VOUCHER PIN
                    |--------------------------------------------------------------------------
                    */

                    'pin' =>
                        $voucher->pin,

                    'status' =>
                        'completed',

                    'wallet_balance' =>
                        (float)
                        $wallet->balance,

                    'sold_at' =>
                        $voucher->sold_at,

                    'expires_at' =>
                        $voucher->expires_at,

                ];
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | GENERATE UNIQUE PURCHASE REFERENCE
    |--------------------------------------------------------------------------
    */

    private function generateReference(): string
    {
        do {

            $reference =
                'VCH-' .
                now()->format(
                    'YmdHis'
                ) .
                '-' .
                strtoupper(
                    Str::random(8)
                );

        } while (
            Transaction::where(
                'reference',
                $reference
            )->exists()
        );

        return $reference;
    }
}