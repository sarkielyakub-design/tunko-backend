<?php

namespace App\Services;

use App\Models\Country;
use App\Models\Office;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\OfficeTransfer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

class OfficeTransferService
{/*
|--------------------------------------------------------------------------
| DESTINATIONS
|--------------------------------------------------------------------------
|
| Returns countries and cities where Tunko currently has active offices.
|
| The office itself is NOT selected by the sender.
|
*/

public function destinations(): array
{
    /*
    |--------------------------------------------------------------------------
    | LOAD ACTIVE OFFICES
    |--------------------------------------------------------------------------
    */

    $offices = Office::query()
        ->where('is_active', true)
        ->orderBy('country')
        ->orderBy('city')
        ->get();

    /*
    |--------------------------------------------------------------------------
    | NO OFFICES
    |--------------------------------------------------------------------------
    */

    if ($offices->isEmpty()) {
        return [];
    }

    /*
    |--------------------------------------------------------------------------
    | GROUP OFFICES BY COUNTRY
    |--------------------------------------------------------------------------
    */

    $grouped = $offices->groupBy(function ($office) {
        return strtolower(
            trim(
                (string) $office->country
            )
        );
    });

    /*
    |--------------------------------------------------------------------------
    | BUILD DESTINATIONS
    |--------------------------------------------------------------------------
    */

    $result = [];

    foreach ($grouped as $countryKey => $countryOffices) {

        if ($countryOffices->isEmpty()) {
            continue;
        }

        /*
        |--------------------------------------------------------------------------
        | COUNTRY NAME
        |--------------------------------------------------------------------------
        */

        $firstOffice =
            $countryOffices->first();

        $countryName =
            trim(
                (string) $firstOffice->country
            );

        if ($countryName === '') {
            continue;
        }

        /*
        |--------------------------------------------------------------------------
        | CITIES
        |--------------------------------------------------------------------------
        */

        $cities = $countryOffices
            ->map(function ($office) {
                return trim(
                    (string) $office->city
                );
            })
            ->filter(function ($city) {
                return $city !== '';
            })
            ->unique()
            ->values()
            ->all();

        if (empty($cities)) {
            continue;
        }

        /*
        |--------------------------------------------------------------------------
        | FIND COUNTRY METADATA
        |--------------------------------------------------------------------------
        |
        | Country table is optional here.
        | If a matching country exists, we use its metadata.
        |
        */

        $country =
            Country::query()
                ->whereRaw(
                    'LOWER(TRIM(name)) = ?',
                    [
                        strtolower(
                            $countryName
                        ),
                    ]
                )
                ->first();

        /*
        |--------------------------------------------------------------------------
        | RESPONSE
        |--------------------------------------------------------------------------
        */

        $result[] = [

            'country_id' =>
                $country?->id,

            'country' =>
                $countryName,

            'iso2' =>
                $country?->iso2,

            'iso3' =>
                $country?->iso3,

            'phone_code' =>
                $country?->phone_code,

            'currency' =>
                $country?->currency
                    ?: $firstOffice->currency,

            'cities' =>
                $cities,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | SORT COUNTRIES
    |--------------------------------------------------------------------------
    */

    usort(
        $result,
        function ($a, $b) {
            return strcasecmp(
                $a['country'],
                $b['country']
            );
        }
    );

    return $result;
}
    /*
    |--------------------------------------------------------------------------
    | QUOTE
    |--------------------------------------------------------------------------
    */

    public function quote(
        User $sender,
        array $data
    ): array {

        $sender->load('wallet');

        /*
        |--------------------------------------------------------------------------
        | Wallet
        |--------------------------------------------------------------------------
        */

        if (!$sender->wallet) {
            throw new Exception(
                'Sender wallet not found.'
            );
        }

        if (!$sender->wallet->is_active) {
            throw new Exception(
                'Your wallet is inactive.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Destination
        |--------------------------------------------------------------------------
        */

        $destination =
            $this->resolveDestination(
                (int) $data['destination_country_id'],
                $data['destination_city']
            );

        $country =
            $destination['country'];

        $city =
            $destination['city'];

        /*
        |--------------------------------------------------------------------------
        | Amount
        |--------------------------------------------------------------------------
        */

        $amount =
            (float) $data['amount'];

        if ($amount <= 0) {
            throw new Exception(
                'Invalid transfer amount.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Currency
        |--------------------------------------------------------------------------
        */

        $currency =
            strtoupper(
                trim(
                    (string) $data['currency']
                )
            );

        if (
            strtoupper(
                $sender->wallet->currency
            ) !== $currency
        ) {
            throw new Exception(
                'Transfer currency does not match your wallet currency.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Fees
        |--------------------------------------------------------------------------
        */

        $feesIncluded =
            (bool) (
                $data['fees_included']
                ?? false
            );

        $fee =
            $this->calculateFee(
                $amount
            );

        /*
        |--------------------------------------------------------------------------
        | Total
        |--------------------------------------------------------------------------
        */

        if ($feesIncluded) {

            /*
             * The entered amount already contains
             * the transfer fee.
             */

            $total =
                round(
                    $amount,
                    2
                );

            $transferAmount =
                round(
                    $amount - $fee,
                    2
                );

            if ($transferAmount <= 0) {
                throw new Exception(
                    'Transfer amount is too small to cover the fee.'
                );
            }

        } else {

            /*
             * Fee is added to the entered amount.
             */

            $transferAmount =
                round(
                    $amount,
                    2
                );

            $total =
                round(
                    $amount + $fee,
                    2
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Balance
        |--------------------------------------------------------------------------
        */

        if (
            (float) $sender->wallet->balance
            < $total
        ) {
            throw new Exception(
                'Insufficient wallet balance.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Quote Response
        |--------------------------------------------------------------------------
        */

        return [

            'amount' =>
                $transferAmount,

            'send_amount' =>
                $transferAmount,

            'fee' =>
                $fee,

            'total' =>
                $total,

            'currency' =>
                $currency,

            'fees_included' =>
                $feesIncluded,

            'destination' => [

                'country_id' =>
                    $country->id,

                'country' =>
                    $country->name,

                'iso2' =>
                    $country->iso2,

                'iso3' =>
                    $country->iso3,

                'city' =>
                    $city,
            ],
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | SEND OFFICE TRANSFER
    |--------------------------------------------------------------------------
    */

    public function send(
        User $sender,
        array $data
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Load Wallet
        |--------------------------------------------------------------------------
        */

        $sender->load('wallet');

        if (!$sender->wallet) {
            throw new Exception(
                'Sender wallet not found.'
            );
        }

        if (!$sender->wallet->is_active) {
            throw new Exception(
                'Your wallet is inactive.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Verify Transaction PIN
        |--------------------------------------------------------------------------
        */

        if (
            empty($sender->transaction_pin)
            ||
            !Hash::check(
                $data['pin'],
                $sender->transaction_pin
            )
        ) {
            throw new Exception(
                'Invalid transaction PIN.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Destination
        |--------------------------------------------------------------------------
        */

        $destination =
            $this->resolveDestination(
                (int) $data['destination_country_id'],
                $data['destination_city']
            );

        $country =
            $destination['country'];

        $city =
            $destination['city'];

        /*
        |--------------------------------------------------------------------------
        | Amount
        |--------------------------------------------------------------------------
        */

        $amount =
            (float) $data['amount'];

        if ($amount <= 0) {
            throw new Exception(
                'Invalid transfer amount.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Currency
        |--------------------------------------------------------------------------
        */

        $currency =
            strtoupper(
                trim(
                    (string) $data['currency']
                )
            );

        if (
            strtoupper(
                $sender->wallet->currency
            ) !== $currency
        ) {
            throw new Exception(
                'Transfer currency does not match your wallet currency.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Fees
        |--------------------------------------------------------------------------
        */

        $feesIncluded =
            (bool) (
                $data['fees_included']
                ?? false
            );

        $fee =
            $this->calculateFee(
                $amount
            );

        if ($feesIncluded) {

            $total =
                round(
                    $amount,
                    2
                );

            $transferAmount =
                round(
                    $amount - $fee,
                    2
                );

            if ($transferAmount <= 0) {
                throw new Exception(
                    'Transfer amount is too small to cover the fee.'
                );
            }

        } else {

            $transferAmount =
                round(
                    $amount,
                    2
                );

            $total =
                round(
                    $amount + $fee,
                    2
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Initial Balance Check
        |--------------------------------------------------------------------------
        */

        if (
            (float) $sender->wallet->balance
            < $total
        ) {
            throw new Exception(
                'Insufficient wallet balance.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Reference
        |--------------------------------------------------------------------------
        */

        $reference =
            $this->generateReference();

        /*
        |--------------------------------------------------------------------------
        | Atomic Transaction
        |--------------------------------------------------------------------------
        */

        return DB::transaction(
            function () use (
                $sender,
                $country,
                $city,
                $transferAmount,
                $fee,
                $total,
                $currency,
                $feesIncluded,
                $reference,
                $data
            ) {

                /*
                |--------------------------------------------------------------------------
                | Lock Sender Wallet
                |--------------------------------------------------------------------------
                */

                $wallet =
                    Wallet::lockForUpdate()
                        ->find(
                            $sender->wallet->id
                        );

                if (!$wallet) {
                    throw new Exception(
                        'Sender wallet not found.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Wallet Status
                |--------------------------------------------------------------------------
                */

                if (!$wallet->is_active) {
                    throw new Exception(
                        'Your wallet is inactive.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Final Balance Check
                |--------------------------------------------------------------------------
                */

                if (
                    (float) $wallet->balance
                    < $total
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

                $wallet->balance =
                    round(
                        (float) $wallet->balance
                        - $total,
                        2
                    );

                $wallet->save();

                /*
                |--------------------------------------------------------------------------
                | Create Office Transfer
                |--------------------------------------------------------------------------
                */

                $transfer =
                    OfficeTransfer::create([

                        'reference' =>
                            $reference,

                        'sender_id' =>
                            $sender->id,

                        /*
                         * Source office is not required
                         * for wallet-to-cash transfer.
                         */
                        'source_office_id' =>
                            null,

                        /*
                         * New destination design.
                         */
                        'destination_country_id' =>
                            $country->id,

                        'destination_city' =>
                            $city,

                        'recipient_phone' =>
                            $data[
                                'recipient_phone'
                            ],

                        'recipient_first_name' =>
                            $data[
                                'recipient_first_name'
                            ],

                        'recipient_last_name' =>
                            $data[
                                'recipient_last_name'
                            ],

                        'amount' =>
                            $transferAmount,

                        'fee' =>
                            $fee,

                        'total' =>
                            $total,

                        'currency' =>
                            $currency,

                        'fees_included' =>
                            $feesIncluded,

                        'reason' =>
                            $data['reason']
                            ?? null,

                        'status' =>
                            'pending',

                        'description' =>
                            $data['description']
                            ?? null,

                        'meta' => [

                            'sender_name' =>
                                $sender->full_name,

                            'sender_phone' =>
                                $sender->phone,

                            'destination_country_id' =>
                                $country->id,

                            'destination_country' =>
                                $country->name,

                            'destination_city' =>
                                $city,

                            'collection_instruction' =>
                                'Recipient can collect cash at any supported Tunko office in the selected city.',
                        ],
                    ]);

                /*
                |--------------------------------------------------------------------------
                | Create Wallet Transaction
                |--------------------------------------------------------------------------
                */

                Transaction::create([

                    'user_id' =>
                        $sender->id,

                    'reference' =>
                        $reference,

                    'type' =>
                        'transfer',

                    'title' =>
                        'Office Transfer',

                    'amount' =>
                        $transferAmount,

                    'fee' =>
                        $fee,

                    'total' =>
                        $total,

                    'currency' =>
                        $currency,

                    'status' =>
                        'completed',

                    'description' =>
                        'Cash transfer to '
                        .
                        $data[
                            'recipient_first_name'
                        ]
                        .
                        ' '
                        .
                        $data[
                            'recipient_last_name'
                        ]
                        .
                        ' - '
                        .
                        $city
                        .
                        ', '
                        .
                        $country->name,

                    'meta' => [

                        'direction' =>
                            'debit',

                        'transfer_type' =>
                            'office_transfer',

                        'office_transfer_id' =>
                            $transfer->id,

                        'recipient_phone' =>
                            $data[
                                'recipient_phone'
                            ],

                        'destination_country_id' =>
                            $country->id,

                        'destination_country' =>
                            $country->name,

                        'destination_city' =>
                            $city,
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
                | Return Result
                |--------------------------------------------------------------------------
                */

                return [

                    'reference' =>
                        $reference,

                    'transfer_id' =>
                        $transfer->id,

                    'status' =>
                        $transfer->status,

                    'amount' =>
                        $transferAmount,

                    'send_amount' =>
                        $transferAmount,

                    'fee' =>
                        $fee,

                    'total' =>
                        $total,

                    'currency' =>
                        $currency,

                    'fees_included' =>
                        $feesIncluded,

                    'recipient' => [

                        'first_name' =>
                            $data[
                                'recipient_first_name'
                            ],

                        'last_name' =>
                            $data[
                                'recipient_last_name'
                            ],

                        'phone' =>
                            $data[
                                'recipient_phone'
                            ],
                    ],

                    'destination' => [

                        'country_id' =>
                            $country->id,

                        'country' =>
                            $country->name,

                        'iso2' =>
                            $country->iso2,

                        'iso3' =>
                            $country->iso3,

                        'city' =>
                            $city,

                        'collection' =>
                            'Any supported Tunko office in this city',
                    ],

                    'wallet_balance' =>
                        (float)
                            $wallet->balance,

                    'created_at' =>
                        $transfer->created_at,
                ];
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | HISTORY
    |--------------------------------------------------------------------------
    */

    public function history(
        User $user
    ) {
        return OfficeTransfer::query()
            ->where(
                'sender_id',
                $user->id
            )
            ->with([
                'destinationCountry',
                'sourceOffice',
            ])
            ->latest()
            ->paginate(20);
    }


    /*
    |--------------------------------------------------------------------------
    | RECEIPT
    |--------------------------------------------------------------------------
    */

    public function receipt(
        string $reference,
        ?User $user = null
    ): array {

        $query =
            OfficeTransfer::query()
                ->with([
                    'sender',
                    'destinationCountry',
                    'sourceOffice',
                ])
                ->where(
                    'reference',
                    $reference
                );

        /*
        |--------------------------------------------------------------------------
        | Security
        |--------------------------------------------------------------------------
        |
        | Currently only the sender can retrieve
        | the receipt through the authenticated API.
        |
        */

        if ($user) {
            $query->where(
                'sender_id',
                $user->id
            );
        }

        $transfer =
            $query->first();

        if (!$transfer) {
            throw new Exception(
                'Office transfer receipt not found.'
            );
        }

        return [

            'reference' =>
                $transfer->reference,

            'status' =>
                $transfer->status,

            'created_at' =>
                optional(
                    $transfer->created_at
                )->toDateTimeString(),

            /*
             * Flutter receipt page expects date.
             */
            'date' =>
                optional(
                    $transfer->created_at
                )->toDateTimeString(),

            'completed_at' =>
                optional(
                    $transfer->completed_at
                )->toDateTimeString(),

            'amount' =>
                (float)
                    $transfer->amount,

            'fee' =>
                (float)
                    $transfer->fee,

            'total' =>
                (float)
                    $transfer->total,

            'currency' =>
                $transfer->currency,

            'fees_included' =>
                (bool)
                    $transfer->fees_included,

            'reason' =>
                $transfer->reason,

            'description' =>
                $transfer->description,

            'recipient' => [

                'first_name' =>
                    $transfer
                        ->recipient_first_name,

                'last_name' =>
                    $transfer
                        ->recipient_last_name,

                'phone' =>
                    $transfer
                        ->recipient_phone,

                'name' =>
                    trim(
                        $transfer
                            ->recipient_first_name
                        .
                        ' '
                        .
                        $transfer
                            ->recipient_last_name
                    ),
            ],

            /*
            |--------------------------------------------------------------------------
            | Destination
            |--------------------------------------------------------------------------
            */

            'destination' => [

                'country_id' =>
                    $transfer
                        ->destination_country_id,

                'country' =>
                    $transfer
                        ->destinationCountry
                        ?->name,

                'iso2' =>
                    $transfer
                        ->destinationCountry
                        ?->iso2,

                'iso3' =>
                    $transfer
                        ->destinationCountry
                        ?->iso3,

                'city' =>
                    $transfer
                        ->destination_city,

                'collection' =>
                    'Recipient can collect cash at any supported Tunko office in this city.',
            ],

            /*
            |--------------------------------------------------------------------------
            | Compatibility
            |--------------------------------------------------------------------------
            |
            | Keep this temporarily so older Flutter code
            | doesn't completely break while we migrate.
            |
            */

            'destination_office' =>
                null,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | RESOLVE DESTINATION
    |--------------------------------------------------------------------------
    |
    | A country + city is valid only if Tunko currently
    | has at least one active office in that location.
    |
    |--------------------------------------------------------------------------
    */

    protected function resolveDestination(
        int $countryId,
        string $city
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Country
        |--------------------------------------------------------------------------
        */

        $country =
            Country::query()
                ->where(
                    'id',
                    $countryId
                )
                ->where(
                    'is_active',
                    true
                )
                ->first();

        if (!$country) {
            throw new Exception(
                'Destination country is not available.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | City
        |--------------------------------------------------------------------------
        */

        $city =
            trim($city);

        if ($city === '') {
            throw new Exception(
                'Destination city is required.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Find Active Tunko Office
        |--------------------------------------------------------------------------
        |
        | The sender does not select this office.
        |
        | We only use an office to verify that Tunko
        | operates in the requested city.
        |
        */

        $countryName =
            strtolower(
                trim(
                    (string) $country->name
                )
            );

        $countryIso2 =
            strtolower(
                trim(
                    (string) $country->iso2
                )
            );

        $countryIso3 =
            strtolower(
                trim(
                    (string) $country->iso3
                )
            );

        $office =
            Office::query()
                ->where(
                    'is_active',
                    true
                )
                ->where(function ($query) use (
                    $countryName,
                    $countryIso2,
                    $countryIso3
                ) {

                    $query
                        ->whereRaw(
                            'LOWER(country) = ?',
                            [
                                $countryName,
                            ]
                        );

                    if ($countryIso2 !== '') {
                        $query->orWhereRaw(
                            'LOWER(country) = ?',
                            [
                                $countryIso2,
                            ]
                        );
                    }

                    if ($countryIso3 !== '') {
                        $query->orWhereRaw(
                            'LOWER(country) = ?',
                            [
                                $countryIso3,
                            ]
                        );
                    }
                })
                ->whereRaw(
                    'LOWER(city) = ?',
                    [
                        strtolower($city),
                    ]
                )
                ->first();

        if (!$office) {
            throw new Exception(
                'Tunko collection is not currently available in this city.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Return Canonical City
        |--------------------------------------------------------------------------
        |
        | Important:
        | We return the city exactly as stored by
        | the Tunko office.
        |
        | Example:
        |
        | User enters:
        | ndjamena
        |
        | Backend returns:
        | N\'Djamena
        |
        |--------------------------------------------------------------------------
        */

        return [

            'country' =>
                $country,

            'city' =>
                $office->city,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | FEE CALCULATION
    |--------------------------------------------------------------------------
    */

    protected function calculateFee(
        float $amount
    ): float {

        if ($amount <= 10000) {
            return 10;
        }

        if ($amount <= 50000) {
            return 25;
        }

        if ($amount <= 100000) {
            return 50;
        }

        return round(
            $amount * 0.005,
            2
        );
    }


    /*
    |--------------------------------------------------------------------------
    | REFERENCE
    |--------------------------------------------------------------------------
    */

    protected function generateReference(): string
    {
        return 'OT'
            .
            Carbon::now()->format(
                'YmdHis'
            )
            .
            strtoupper(
                Str::random(6)
            );
    }
}