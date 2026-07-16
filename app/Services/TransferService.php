<?php

namespace App\Services;

use App\Models\Recipient;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Exception;

class TransferService
{
    public function transfer(User $sender, array $data): array
    {
        /*
        |--------------------------------------------------------------------------
        | Verify PIN
        |--------------------------------------------------------------------------
        */

        if (!Hash::check(
            $data['pin'],
            $sender->transaction_pin
        )) {
            throw new Exception('Invalid transaction PIN.');
        }

        /*
        |--------------------------------------------------------------------------
        | Find Recipient
        |--------------------------------------------------------------------------
        */

        $recipient = User::where(
            'phone',
            $data['recipient_phone']
        )->first();

        if (!$recipient) {
            throw new Exception('Recipient not found.');
        }

        if ($recipient->id === $sender->id) {
            throw new Exception(
                'You cannot transfer to yourself.'
            );
        }

        if (!$sender->wallet) {
            throw new Exception('Sender wallet not found.');
        }

        if (!$recipient->wallet) {
            throw new Exception('Recipient wallet not found.');
        }

        if (
            $sender->wallet->balance <
            $data['amount']
        ) {
            throw new Exception(
                'Insufficient wallet balance.'
            );
        }

        return DB::transaction(function () use (
            $sender,
            $recipient,
            $data
        ) {

            $reference =
                'TNK' .
                strtoupper(Str::random(12));

            /*
            |--------------------------------------------------------------------------
            | Debit Sender
            |--------------------------------------------------------------------------
            */

            $sender->wallet->decrement(
                'balance',
                $data['amount']
            );

            /*
            |--------------------------------------------------------------------------
            | Credit Recipient
            |--------------------------------------------------------------------------
            */

            $recipient->wallet->increment(
                'balance',
                $data['amount']
            );

            /*
            |--------------------------------------------------------------------------
            | Transfer Record
            |--------------------------------------------------------------------------
            */

            $transfer = Transfer::create([

                'user_id' => $sender->id,

                'recipient_id' => $recipient->id,

                'reference' => $reference,

                'destination_country' =>
                    $recipient->country,

                'destination_currency' =>
                    $recipient->wallet->currency,

                'send_amount' =>
                    $data['amount'],

                'receive_amount' =>
                    $data['amount'],

                'exchange_rate' => 1,

                'fee' => 0,

                'total' =>
                    $data['amount'],

                'status' => 'completed',

                'remark' =>
                    $data['remark'] ?? null,

            ]);

            /*
            |--------------------------------------------------------------------------
            | Sender Transaction
            |--------------------------------------------------------------------------
            */

            Transaction::create([

                'user_id' => $sender->id,

                'reference' => $reference,

                'type' => 'wallet_transfer',

                'title' => 'Wallet Transfer',

                'description' =>
                    'Transfer to ' .
                    $recipient->phone,

                'amount' =>
                    $data['amount'],

                'currency' =>
                    $sender->wallet->currency,

                'fee' => 0,

                'total' =>
                    $data['amount'],

                'status' => 'completed',

                'recipient' =>
                    $recipient->phone,

                'sender' =>
                    $sender->phone,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Recipient Transaction
            |--------------------------------------------------------------------------
            */

            Transaction::create([

                'user_id' => $recipient->id,

                'reference' => $reference,

                'type' => 'wallet_received',

                'title' => 'Wallet Credit',

                'description' =>
                    'Received from ' .
                    $sender->phone,

                'amount' =>
                    $data['amount'],

                'currency' =>
                    $recipient->wallet->currency,

                'fee' => 0,

                'total' =>
                    $data['amount'],

                'status' => 'completed',

                'recipient' =>
                    $recipient->phone,

                'sender' =>
                    $sender->phone,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Save Recipient
            |--------------------------------------------------------------------------
            */

            Recipient::firstOrCreate(

                [
                    'user_id' =>
                        $sender->id,

                    'wallet_number' =>
                        $recipient
                            ->wallet
                            ->wallet_number,
                ],

                [
                    'name' =>
                        $recipient->first_name .
                        ' ' .
                        $recipient->last_name,

                    'phone' =>
                        $recipient->phone,

                    'country' =>
                        $recipient->country,

                    'currency' =>
                        $recipient
                            ->wallet
                            ->currency,
                ]
            );

            return [

                'reference' => $reference,

                'transfer' => $transfer,

                'wallet_balance' =>
                    $sender
                        ->wallet
                        ->fresh()
                        ->balance,

            ];
        });
    }
}