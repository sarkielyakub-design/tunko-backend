<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transfer\VerifyRecipientRequest;
use App\Http\Requests\Wallet\TransferRequest;
use App\Http\Resources\RecipientResource;
use App\Models\Country;
use App\Models\Recipient;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Throwable;

class TransferController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | SEND TRANSFER
    |--------------------------------------------------------------------------
    |
    | Current Flutter request:
    |
    | {
    |     recipient: "phone/wallet/email/username",
    |     amount: 5000,
    |     pin: "5775",
    |     description: null
    | }
    |
    */

    public function transfer(TransferRequest $request)
    {
        $sender = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Load Sender Wallet
        |--------------------------------------------------------------------------
        */

        $sender->load('wallet');

        if (!$sender->wallet) {
            return response()->json([
                'success' => false,
                'message' => 'Wallet not found.',
            ], 404);
        }

        if (!$sender->wallet->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Wallet is inactive.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Verify Transaction PIN
        |--------------------------------------------------------------------------
        */

        if (
            empty($sender->transaction_pin) ||
            !Hash::check(
                $request->pin,
                $sender->transaction_pin
            )
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid transaction PIN.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Find Recipient
        |--------------------------------------------------------------------------
        |
        | Recipient can be:
        |
        | - Phone number
        | - Username
        | - Email
        | - Wallet number
        |
        */

        $search = trim($request->recipient);

        $recipient = $this->findRecipient($search);

        /*
        |--------------------------------------------------------------------------
        | Recipient Not Found
        |--------------------------------------------------------------------------
        */

        if (!$recipient) {
            return response()->json([
                'success' => false,
                'message' => 'Recipient not found.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Verify Recipient Wallet
        |--------------------------------------------------------------------------
        */

        if (!$recipient->wallet) {
            return response()->json([
                'success' => false,
                'message' => 'Recipient wallet unavailable.',
            ], 422);
        }

        if (!$recipient->wallet->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Recipient wallet is inactive.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Prevent Self Transfer
        |--------------------------------------------------------------------------
        */

        if ($sender->id === $recipient->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot transfer to yourself.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Amount
        |--------------------------------------------------------------------------
        */

        $amount = (float) $request->amount;

        /*
        |--------------------------------------------------------------------------
        | Calculate Fee
        |--------------------------------------------------------------------------
        */

        $fee = $this->calculateFee($amount);

        $total = $amount + $fee;

        /*
        |--------------------------------------------------------------------------
        | Initial Balance Check
        |--------------------------------------------------------------------------
        */

        if ((float) $sender->wallet->balance < $total) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient wallet balance.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Database Transaction
        |--------------------------------------------------------------------------
        */

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | Lock Sender Wallet
            |--------------------------------------------------------------------------
            */

            $senderWallet = $sender->wallet
                ->newQuery()
                ->lockForUpdate()
                ->find($sender->wallet->id);

            if (!$senderWallet) {
                throw new \Exception(
                    'Sender wallet not found.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Lock Recipient Wallet
            |--------------------------------------------------------------------------
            */

            $recipientWallet = $recipient->wallet
                ->newQuery()
                ->lockForUpdate()
                ->find($recipient->wallet->id);

            if (!$recipientWallet) {
                throw new \Exception(
                    'Recipient wallet not found.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Verify Balance Again
            |--------------------------------------------------------------------------
            */

            if ((float) $senderWallet->balance < $total) {
                throw new \Exception(
                    'Insufficient wallet balance.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Generate Reference
            |--------------------------------------------------------------------------
            */

            $reference =
                'TNK' .
                now()->format('YmdHis') .
                strtoupper(Str::random(6));

            /*
            |--------------------------------------------------------------------------
            | Debit Sender
            |--------------------------------------------------------------------------
            */

            $senderWallet->balance =
                (float) $senderWallet->balance - $total;

            $senderWallet->save();

            /*
            |--------------------------------------------------------------------------
            | Credit Recipient
            |--------------------------------------------------------------------------
            */

            $recipientWallet->balance =
                (float) $recipientWallet->balance + $amount;

            $recipientWallet->save();

            /*
            |--------------------------------------------------------------------------
            | Save Transfer
            |--------------------------------------------------------------------------
            */

            $transfer = Transfer::create([

                'user_id' =>
                    $sender->id,

                'recipient_id' =>
                    $recipient->id,

                'reference' =>
                    $reference,

                'destination_country' =>
                    $recipient->country ?? null,

                'destination_currency' =>
                    $recipientWallet->currency,

                'send_amount' =>
                    $amount,

                'receive_amount' =>
                    $amount,

                'exchange_rate' =>
                    1,

                'fee' =>
                    $fee,

                'total' =>
                    $total,

                'status' =>
                    'completed',

                'remark' =>
                    $request->description,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Sender Transaction
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
                    'Money Transfer',

                'amount' =>
                    $amount,

                'fee' =>
                    $fee,

                'total' =>
                    $total,

                'currency' =>
                    $senderWallet->currency,

                'status' =>
                    'completed',

                'description' =>
                    'Transfer to ' .
                    $recipient->full_name,

                'meta' => [

                    'direction' =>
                        'debit',

                    'recipient_id' =>
                        $recipient->id,

                    'recipient_name' =>
                        $recipient->full_name,

                    'recipient_phone' =>
                        $recipient->phone,

                    'recipient_wallet' =>
                        $recipientWallet->wallet_number,

                    'currency' =>
                        $senderWallet->currency,
                ],
            ]);

            /*
            |--------------------------------------------------------------------------
            | Recipient Transaction
            |--------------------------------------------------------------------------
            */

            Transaction::create([

                'user_id' =>
                    $recipient->id,

                'reference' =>
                    $reference,

                'type' =>
                    'transfer',

                'title' =>
                    'Money Received',

                'amount' =>
                    $amount,

                'fee' =>
                    0,

                'total' =>
                    $amount,

                'currency' =>
                    $recipientWallet->currency,

                'status' =>
                    'completed',

                'description' =>
                    'Received from ' .
                    $sender->full_name,

                'meta' => [

                    'direction' =>
                        'credit',

                    'sender_id' =>
                        $sender->id,

                    'sender_name' =>
                        $sender->full_name,

                    'sender_phone' =>
                        $sender->phone,

                    'sender_wallet' =>
                        $senderWallet->wallet_number,

                    'currency' =>
                        $recipientWallet->currency,
                ],
            ]);

            /*
            |--------------------------------------------------------------------------
            | Save Recent Recipient
            |--------------------------------------------------------------------------
            */

            Recipient::firstOrCreate(

                [
                    'user_id' =>
                        $sender->id,

                    'wallet_number' =>
                        $recipientWallet->wallet_number,
                ],

                [
                    'name' =>
                        $recipient->full_name,

                    'phone' =>
                        $recipient->phone,

                    'country' =>
                        $recipient->country,

                    'currency' =>
                        $recipientWallet->currency,
                ]

            );

            /*
            |--------------------------------------------------------------------------
            | Commit
            |--------------------------------------------------------------------------
            */

            DB::commit();

            /*
            |--------------------------------------------------------------------------
            | Refresh Sender Wallet
            |--------------------------------------------------------------------------
            */

            $senderWallet->refresh();

            /*
            |--------------------------------------------------------------------------
            | Success
            |--------------------------------------------------------------------------
            */

            return response()->json([

                'success' =>
                    true,

                'message' =>
                    'Transfer completed successfully.',

                'data' => [

                    'reference' =>
                        $reference,

                    'transaction_id' =>
                        $transfer->id,

                    'recipient' => [

                        'id' =>
                            $recipient->id,

                        'first_name' =>
                            $recipient->first_name,

                        'last_name' =>
                            $recipient->last_name,

                        'name' =>
                            $recipient->full_name,

                        'phone' =>
                            $recipient->phone,

                        'email' =>
                            $recipient->email,

                        'wallet_number' =>
                            $recipientWallet->wallet_number,

                        'country' =>
                            $recipient->country,

                        'currency' =>
                            $recipientWallet->currency,
                    ],

                    'amount' =>
                        $amount,

                    'send_amount' =>
                        $amount,

                    'receive_amount' =>
                        $amount,

                    'fee' =>
                        $fee,

                    'total' =>
                        $total,

                    'exchange_rate' =>
                        1,

                    'currency' =>
                        $senderWallet->currency,

                    'status' =>
                        'completed',

                    'remark' =>
                        $request->description,

                    'wallet_balance' =>
                        (float) $senderWallet->balance,

                    'created_at' =>
                        $transfer->created_at,
                ],
            ]);

        } catch (Throwable $e) {

            DB::rollBack();

            report($e);

            return response()->json([

                'success' =>
                    false,

                'message' =>
                    'Transfer failed.',

                'error' =>
                    $e->getMessage(),

            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | FIND RECIPIENT
    |--------------------------------------------------------------------------
    */

    private function findRecipient(
        string $search
    ): ?User {

        return User::with('wallet')
            ->where(function ($query) use ($search) {

                $query

                    ->where(
                        'phone',
                        $search
                    )

                    ->orWhere(
                        'username',
                        $search
                    )

                    ->orWhere(
                        'email',
                        $search
                    )

                    ->orWhereHas(
                        'wallet',
                        function ($walletQuery) use ($search) {

                            $walletQuery->where(
                                'wallet_number',
                                $search
                            );

                        }
                    );
            })
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | CALCULATE FEE
    |--------------------------------------------------------------------------
    */

    private function calculateFee(
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
    | SEARCH RECIPIENT
    |--------------------------------------------------------------------------
    */

    public function searchRecipient(
        Request $request
    ) {

        $request->validate([

            'query' => [
                'required',
                'string',
                'min:2',
            ],

        ]);

        $search = trim(
            $request->input('query')
        );

        $user = $this->findRecipient(
            $search
        );

        if (!$user) {

            return response()->json([

                'success' =>
                    false,

                'message' =>
                    'Recipient not found.',

            ], 404);
        }

        if (!$user->wallet) {

            return response()->json([

                'success' =>
                    false,

                'message' =>
                    'Recipient wallet not found.',

            ], 404);
        }

        if (
            $user->id ===
            $request->user()->id
        ) {

            return response()->json([

                'success' =>
                    false,

                'message' =>
                    'You cannot transfer to yourself.',

            ], 422);
        }

        return response()->json([

            'success' =>
                true,

            'message' =>
                'Recipient found.',

            'data' => $this->recipientData(
                $user
            ),

        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | VERIFY RECIPIENT
    |--------------------------------------------------------------------------
    */

    public function verifyWalletRecipient(
        Request $request
    ) {

        $request->validate([

            'query' => [
                'required',
                'string',
                'min:2',
            ],

        ]);

        $search = trim(
            $request->input('query')
        );

        $recipient =
            $this->findRecipient(
                $search
            );

        if (!$recipient) {

            return response()->json([

                'success' =>
                    false,

                'message' =>
                    'Recipient not found.',

            ], 404);
        }

        if (!$recipient->wallet) {

            return response()->json([

                'success' =>
                    false,

                'message' =>
                    'Recipient wallet not found.',

            ], 404);
        }

        if (
            $recipient->id ===
            $request->user()->id
        ) {

            return response()->json([

                'success' =>
                    false,

                'message' =>
                    'You cannot transfer to yourself.',

            ], 422);
        }

        return response()->json([

            'success' =>
                true,

            'message' =>
                'Recipient verified successfully.',

            'data' => $this->recipientData(
                $recipient
            ),

        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | RECIPIENT DATA
    |--------------------------------------------------------------------------
    */

    private function recipientData(
        User $user
    ): array {

        return [

            'id' =>
                $user->id,

            'first_name' =>
                $user->first_name,

            'last_name' =>
                $user->last_name,

            'full_name' =>
                $user->full_name,

            'username' =>
                $user->username,

            'email' =>
                $user->email,

            'phone' =>
                $user->phone,

            'wallet_number' =>
                optional(
                    $user->wallet
                )->wallet_number,

            'country' =>
                $user->country,

            'currency' =>
                optional(
                    $user->wallet
                )->currency,

            'is_verified' =>
                (bool) $user->is_verified,

        ];
    }

    /*
    |--------------------------------------------------------------------------
    | COUNTRIES
    |--------------------------------------------------------------------------
    */

    public function countries()
    {

        $countries = Country::where(
            'is_active',
            true
        )
        ->orderBy('name')
        ->get();

        return response()->json([

            'success' =>
                true,

            'message' =>
                'Countries retrieved successfully.',

            'data' =>
                $countries->map(
                    function ($country) {

                        return [

                            'id' =>
                                $country->id,

                            'name' =>
                                $country->name,

                            'iso2' =>
                                $country->iso2,

                            'iso3' =>
                                $country->iso3,

                            'phone_code' =>
                                $country->phone_code,

                            'currency' =>
                                $country->currency,

                            'currency_symbol' =>
                                $country->currency_symbol,

                            'exchange_rate' =>
                                (float)
                                $country->exchange_rate,

                            'flag' =>
                                $country->flag,

                            'is_active' =>
                                (bool)
                                $country->is_active,
                        ];
                    }
                ),

        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | TRANSFER QUOTE
    |--------------------------------------------------------------------------
    */

    public function quote(
        Request $request
    ) {

        $request->validate([

            'country_id' => [
                'required',
                'exists:countries,id',
            ],

            'amount' => [
                'required',
                'numeric',
                'min:1',
            ],

        ]);

        $country =
            Country::findOrFail(
                $request->country_id
            );

        $exchangeRate =
            (float) $country->exchange_rate;

        $amount =
            (float) $request->amount;

        $fee =
            $this->calculateFee(
                $amount
            );

        $receiveAmount =
            round(
                $amount * $exchangeRate,
                2
            );

        $total =
            $amount + $fee;

        return response()->json([

            'success' =>
                true,

            'message' =>
                'Transfer quote generated successfully.',

            'data' => [

                'country' => [

                    'id' =>
                        $country->id,

                    'name' =>
                        $country->name,

                    'currency' =>
                        $country->currency,

                    'currency_symbol' =>
                        $country->currency_symbol,

                    'flag' =>
                        $country->flag,
                ],

                'exchange_rate' =>
                    $exchangeRate,

                'send_amount' =>
                    $amount,

                'receive_amount' =>
                    $receiveAmount,

                'fee' =>
                    $fee,

                'total' =>
                    $total,
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | TRANSFER HISTORY
    |--------------------------------------------------------------------------
    */

    public function history(
        Request $request
    ) {

        $query = Transfer::where(
            'user_id',
            $request->user()->id
        );

        if ($request->filled('reference')) {

            $query->where(
                'reference',
                'like',
                '%' .
                $request->reference .
                '%'
            );
        }

        if ($request->filled('status')) {

            $query->where(
                'status',
                $request->status
            );
        }

        if ($request->filled('from')) {

            $query->whereDate(
                'created_at',
                '>=',
                $request->from
            );
        }

        if ($request->filled('to')) {

            $query->whereDate(
                'created_at',
                '<=',
                $request->to
            );
        }

        $transfers =
            $query
                ->latest()
                ->paginate(
                    min(
                        (int) $request->get(
                            'per_page',
                            20
                        ),
                        100
                    )
                );

        $userId =
            $request->user()->id;

        $summary = [

            'total_transfers' =>
                Transfer::where(
                    'user_id',
                    $userId
                )->count(),

            'total_amount' =>
                Transfer::where(
                    'user_id',
                    $userId
                )->sum(
                    'send_amount'
                ),

            'completed' =>
                Transfer::where(
                    'user_id',
                    $userId
                )
                ->where(
                    'status',
                    'completed'
                )
                ->count(),

            'pending' =>
                Transfer::where(
                    'user_id',
                    $userId
                )
                ->where(
                    'status',
                    'pending'
                )
                ->count(),

            'failed' =>
                Transfer::where(
                    'user_id',
                    $userId
                )
                ->where(
                    'status',
                    'failed'
                )
                ->count(),
        ];

        return response()->json([

            'success' =>
                true,

            'summary' =>
                $summary,

            'data' =>
                $transfers,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | SAVED / RECENT RECIPIENTS
    |--------------------------------------------------------------------------
    */

    public function recipients(
        Request $request
    ) {

        $recipients =
            Recipient::where(
                'user_id',
                $request->user()->id
            )
            ->latest()
            ->get();

        return response()->json([

            'success' =>
                true,

            'message' =>
                'Recipients retrieved successfully.',

            'data' =>
                $recipients,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | RECEIPT
    |--------------------------------------------------------------------------
    */

    public function receipt(
        Request $request,
        string $reference
    ) {

        $transfer = Transfer::with([
            'user.wallet',
            'recipient.wallet',
        ])
        ->where(
            'reference',
            $reference
        )
        ->where(
            'user_id',
            $request->user()->id
        )
        ->first();

        if (!$transfer) {

            return response()->json([

                'success' =>
                    false,

                'message' =>
                    'Transfer receipt not found.',

            ], 404);
        }

        return response()->json([

            'success' =>
                true,

            'message' =>
                'Transfer receipt retrieved successfully.',

            'data' => [

                'reference' =>
                    $transfer->reference,

                'status' =>
                    $transfer->status,

                'remark' =>
                    $transfer->remark,

                'send_amount' =>
                    (float)
                    $transfer->send_amount,

                'receive_amount' =>
                    (float)
                    $transfer->receive_amount,

                'fee' =>
                    (float)
                    $transfer->fee,

                'total' =>
                    (float)
                    $transfer->total,

                'exchange_rate' =>
                    (float)
                    $transfer->exchange_rate,

                'currency' =>
                    $transfer->destination_currency,

                'created_at' =>
                    $transfer->created_at,

                /*
                |--------------------------------------------------------------------------
                | Sender
                |--------------------------------------------------------------------------
                */

                'sender' => [

                    'id' =>
                        $transfer->user->id,

                    'first_name' =>
                        $transfer->user->first_name,

                    'last_name' =>
                        $transfer->user->last_name,

                    'name' =>
                        $transfer->user->full_name,

                    'email' =>
                        $transfer->user->email,

                    'phone' =>
                        $transfer->user->phone,

                    'wallet_number' =>
                        optional(
                            $transfer->user->wallet
                        )->wallet_number,

                ],

                /*
                |--------------------------------------------------------------------------
                | Recipient
                |--------------------------------------------------------------------------
                */

                'recipient' => [

                    'id' =>
                        $transfer->recipient->id,

                    'first_name' =>
                        $transfer->recipient->first_name,

                    'last_name' =>
                        $transfer->recipient->last_name,

                    'name' =>
                        $transfer->recipient->full_name,

                    'email' =>
                        $transfer->recipient->email,

                    'phone' =>
                        $transfer->recipient->phone,

                    'wallet_number' =>
                        optional(
                            $transfer->recipient->wallet
                        )->wallet_number,

                    'country' =>
                        $transfer->destination_country,

                    'currency' =>
                        optional(
                            $transfer->recipient->wallet
                        )->currency,

                ],
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | VERIFY RECIPIENT REQUEST
    |--------------------------------------------------------------------------
    |
    | This method is retained only if your routes currently use
    | a dedicated VerifyRecipientRequest endpoint.
    |
    */

    public function verify(
        VerifyRecipientRequest $request
    ) {

        $query = trim(
            $request->validated()['query']
            ?? ''
        );

        $user = $this->findRecipient(
            $query
        );

        if (!$user) {

            return response()->json([

                'success' =>
                    false,

                'message' =>
                    'Recipient not found.',

            ], 404);
        }

        if (!$user->wallet) {

            return response()->json([

                'success' =>
                    false,

                'message' =>
                    'Recipient wallet not found.',

            ], 404);
        }

        if (
            $user->id ===
            $request->user()->id
        ) {

            return response()->json([

                'success' =>
                    false,

                'message' =>
                    'You cannot transfer to yourself.',

            ], 422);
        }

        return response()->json([

            'success' =>
                true,

            'message' =>
                'Recipient verified successfully.',

            'data' =>
                $this->recipientData(
                    $user
                ),

        ]);
    }
}