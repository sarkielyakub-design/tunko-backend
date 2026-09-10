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
    $offices = Office::query()
        ->where('is_active', true)
        ->orderBy('country')
        ->orderBy('city')
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get();

    if ($offices->isEmpty()) {
        return [];
    }

    $result = [];
    foreach ($offices->groupBy(fn ($office) => strtolower(trim((string) $office->country))) as $countryOffices) {
        $first = $countryOffices->first();
        $countryName = trim((string) $first->country);
        if ($countryName === '') continue;

        $country = Country::query()
            ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($countryName)])
            ->orWhereRaw('LOWER(TRIM(iso2)) = ?', [strtolower($countryName)])
            ->orWhereRaw('LOWER(TRIM(iso3)) = ?', [strtolower($countryName)])
            ->first();

        $result[] = [
            'country_id' => $country?->id,
            'country' => $countryName,
            'iso2' => $country?->iso2,
            'iso3' => $country?->iso3,
            'phone_code' => $country?->phone_code,
            'currency' => $country?->currency ?: $first->currency,
            'cities' => $countryOffices->pluck('city')->filter()->unique()->values()->all(),
            'offices' => $countryOffices->map(fn ($office) => [
                'id' => $office->id,
                'name' => $office->name,
                'slug' => $office->slug,
                'country' => $office->country,
                'state' => $office->state,
                'city' => $office->city,
                'address' => $office->address,
                'phone' => $office->phone,
                'whatsapp' => $office->whatsapp,
                'email' => $office->email,
                'latitude' => $office->latitude,
                'longitude' => $office->longitude,
                'timezone' => $office->timezone,
                'currency' => $office->currency,
                'is_head_office' => (bool) $office->is_head_office,
            ])->values()->all(),
        ];
    }

    usort($result, fn ($a, $b) => strcasecmp($a['country'], $b['country']));
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
                $data['destination_office_id'] ?? null,
                isset($data['destination_country_id']) ? (int) $data['destination_country_id'] : null,
                $data['destination_city'] ?? null
            );

        $country =
            $destination['country'];

        $city =
            $destination['city'];

        $office =
            $destination['office'];

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

                'office_id' =>
                    $office->id,

                'office_name' =>
                    $office->name,

                'office_address' =>
                    $office->address,
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
                $data['destination_office_id'] ?? null,
                isset($data['destination_country_id']) ? (int) $data['destination_country_id'] : null,
                $data['destination_city'] ?? null
            );

        $country =
            $destination['country'];

        $city =
            $destination['city'];

        $office =
            $destination['office'];

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
                $office,
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
                        'destination_office_id' =>
                            $office->id,

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

                        'destination_office_id' =>
                            $office->id,
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

                        'office_id' =>
                            $office->id,

                        'office_name' =>
                            $office->name,

                        'office_address' =>
                            $office->address,

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
                'destinationOffice',
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
                    'destinationOffice',
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
                $transfer->destinationOffice
                    ? [
                        'id' => $transfer->destinationOffice->id,
                        'name' => $transfer->destinationOffice->name,
                        'country' => $transfer->destinationOffice->country,
                        'city' => $transfer->destinationOffice->city,
                        'address' => $transfer->destinationOffice->address,
                        'phone' => $transfer->destinationOffice->phone,
                    ]
                    : null,
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
        ?int $officeId,
        ?int $countryId,
        ?string $city
    ): array {
        if ($officeId) {
            $office = Office::query()
                ->whereKey($officeId)
                ->where('is_active', true)
                ->first();

            if (!$office) {
                throw new Exception('Destination office is not available.');
            }

            $country = Country::query()
                ->where('is_active', true)
                ->where(function ($query) use ($office) {
                    $query->whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim((string) $office->country))]);
                    if ($office->country) {
                        $query->orWhereRaw('LOWER(TRIM(iso2)) = ?', [strtolower(trim((string) $office->country))]);
                        $query->orWhereRaw('LOWER(TRIM(iso3)) = ?', [strtolower(trim((string) $office->country))]);
                    }
                })
                ->first();

            if (!$country && $countryId) {
                $country = Country::query()->whereKey($countryId)->where('is_active', true)->first();
            }

            if (!$country) {
                throw new Exception('Destination country for this office is not available.');
            }

            return [
                'country' => $country,
                'city' => $office->city,
                'office' => $office,
            ];
        }

        if (!$countryId || !$city) {
            throw new Exception('Destination office is required.');
        }

        $country = Country::query()
            ->whereKey($countryId)
            ->where('is_active', true)
            ->first();

        if (!$country) {
            throw new Exception('Destination country is not available.');
        }

        $city = trim($city);
        if ($city === '') {
            throw new Exception('Destination city is required.');
        }

        $names = array_values(array_filter([
            strtolower(trim((string) $country->name)),
            strtolower(trim((string) $country->iso2)),
            strtolower(trim((string) $country->iso3)),
        ]));

        $office = Office::query()
            ->where('is_active', true)
            ->whereRaw('LOWER(TRIM(city)) = ?', [strtolower($city)])
            ->where(function ($query) use ($names) {
                foreach ($names as $i => $name) {
                    $method = $i === 0 ? 'whereRaw' : 'orWhereRaw';
                    $query->{$method}('LOWER(TRIM(country)) = ?', [$name]);
                }
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        if (!$office) {
            throw new Exception('Tunko collection is not currently available in this city.');
        }

        return [
            'country' => $country,
            'city' => $office->city,
            'office' => $office,
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