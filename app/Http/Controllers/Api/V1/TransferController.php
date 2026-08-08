<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wallet\TransferRequest;
use App\Models\Recipient;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Throwable;
use App\Http\Requests\Transfer\VerifyRecipientRequest;
use App\Http\Resources\RecipientResource;
use App\Models\Country;
use App\Http\Resources\CountryResource;
use Illuminate\Http\Request;
class TransferController extends Controller
{
 
 public function transfer(TransferRequest $request)
{
    $sender = $request->user();

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

            "success" => false,

            "message" =>
                "Invalid transaction PIN."

        ], 422);
    }

    /*
    |--------------------------------------------------------------------------
    | Verify Sender Wallet
    |--------------------------------------------------------------------------
    */

    $sender->load('wallet');

    if (!$sender->wallet) {

        return response()->json([

            "success" => false,

            "message" =>
                "Wallet not found."

        ], 404);
    }

    if (!$sender->wallet->is_active) {

        return response()->json([

            "success" => false,

            "message" =>
                "Wallet is inactive."

        ], 422);
    }

    /*
    |--------------------------------------------------------------------------
    | Find Recipient
    |--------------------------------------------------------------------------
    |
    | The Flutter app sends:
    |
    | recipient = wallet number / phone / username / email
    |
    */

    $search = trim(
        $request->recipient
    );

    $recipient = User::with('wallet')
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

    /*
    |--------------------------------------------------------------------------
    | Recipient Not Found
    |--------------------------------------------------------------------------
    */

    if (!$recipient) {

        return response()->json([

            "success" => false,

            "message" =>
                "Recipient not found."

        ], 422);
    }

    /*
    |--------------------------------------------------------------------------
    | Recipient Wallet
    |--------------------------------------------------------------------------
    */

    if (!$recipient->wallet) {

        return response()->json([

            "success" => false,

            "message" =>
                "Recipient wallet unavailable."

        ], 422);
    }

    if (!$recipient->wallet->is_active) {

        return response()->json([

            "success" => false,

            "message" =>
                "Recipient wallet is inactive."

        ], 422);
    }

    /*
    |--------------------------------------------------------------------------
    | Prevent Self Transfer
    |--------------------------------------------------------------------------
    */

    if (
        $sender->id ===
        $recipient->id
    ) {

        return response()->json([

            "success" => false,

            "message" =>
                "You cannot transfer to yourself."

        ], 422);
    }

    /*
    |--------------------------------------------------------------------------
    | Calculate Transfer Fee
    |--------------------------------------------------------------------------
    */

    $amount =
        (float) $request->amount;

    if ($amount <= 10000) {

        $fee = 10;

    } elseif ($amount <= 50000) {

        $fee = 25;

    } elseif ($amount <= 100000) {

        $fee = 50;

    } else {

        $fee = round(
            $amount * 0.005,
            2
        );
    }

    $total =
        $amount + $fee;

    /*
    |--------------------------------------------------------------------------
    | Check Sender Balance
    |--------------------------------------------------------------------------
    */

    if (
        (float) $sender->wallet->balance
        < $total
    ) {

        return response()->json([

            "success" => false,

            "message" =>
                "Insufficient wallet balance."

        ], 422);
    }

    /*
    |--------------------------------------------------------------------------
    | Begin Database Transaction
    |--------------------------------------------------------------------------
    */

    DB::beginTransaction();

    try {

        /*
        |--------------------------------------------------------------------------
        | Lock Sender Wallet
        |--------------------------------------------------------------------------
        */

        $senderWallet =
            $sender->wallet
                ->newQuery()
                ->lockForUpdate()
                ->find(
                    $sender->wallet->id
                );

        if (!$senderWallet) {

            throw new \Exception(
                "Sender wallet not found."
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Lock Recipient Wallet
        |--------------------------------------------------------------------------
        */

        $recipientWallet =
            $recipient->wallet
                ->newQuery()
                ->lockForUpdate()
                ->find(
                    $recipient->wallet->id
                );

        if (!$recipientWallet) {

            throw new \Exception(
                "Recipient wallet not found."
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Check Balance Again
        |--------------------------------------------------------------------------
        |
        | Important because another transaction could have
        | changed the balance after the first check.
        |
        */

        if (
            (float) $senderWallet->balance
            < $total
        ) {

            throw new \Exception(
                "Insufficient wallet balance."
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Generate Reference
        |--------------------------------------------------------------------------
        */

        $reference =
            "TNK" .
            strtoupper(
                Str::random(12)
            );

        /*
        |--------------------------------------------------------------------------
        | Debit Sender
        |--------------------------------------------------------------------------
        */

        $senderWallet->balance =
            (float) $senderWallet->balance
            - $total;

        $senderWallet->save();

        /*
        |--------------------------------------------------------------------------
        | Credit Recipient
        |--------------------------------------------------------------------------
        */

        $recipientWallet->balance =
            (float) $recipientWallet->balance
            + $amount;

        $recipientWallet->save();

        /*
        |--------------------------------------------------------------------------
        | Save Transfer
        |--------------------------------------------------------------------------
        */

        $transfer = Transfer::create([

            "user_id" =>
                $sender->id,

            "recipient_id" =>
                $recipient->id,

            "reference" =>
                $reference,

            "destination_country" =>
                $recipient->country,

            "destination_currency" =>
                $recipientWallet->currency,

            "send_amount" =>
                $amount,

            "receive_amount" =>
                $amount,

            "exchange_rate" =>
                1,

            "fee" =>
                $fee,

            "total" =>
                $total,

            "status" =>
                "completed",

            "remark" =>
                $request->description,

        ]);

        /*
        |--------------------------------------------------------------------------
        | Sender Transaction
        |--------------------------------------------------------------------------
        */

        Transaction::create([

            "user_id" =>
                $sender->id,

            "reference" =>
                $reference,

            "type" =>
                "transfer",

            "title" =>
                "Money Transfer",

            "amount" =>
                $amount,

            "fee" =>
                $fee,

            "total" =>
                $total,

            "currency" =>
                $senderWallet->currency,

            "status" =>
                "completed",

            "description" =>
                "Transfer to " .
                $recipient->full_name,

            "meta" => [

                "direction" =>
                    "debit",

                "recipient_id" =>
                    $recipient->id,

                "recipient_name" =>
                    $recipient->full_name,

                "recipient_phone" =>
                    $recipient->phone,

                "recipient_wallet" =>
                    $recipientWallet->wallet_number,

                "currency" =>
                    $senderWallet->currency,

            ],

        ]);

        /*
        |--------------------------------------------------------------------------
        | Recipient Transaction
        |--------------------------------------------------------------------------
        */

        Transaction::create([

            "user_id" =>
                $recipient->id,

            "reference" =>
                $reference,

            "type" =>
                "transfer",

            "title" =>
                "Money Received",

            "amount" =>
                $amount,

            "fee" =>
                0,

            "total" =>
                $amount,

            "currency" =>
                $recipientWallet->currency,

            "status" =>
                "completed",

            "description" =>
                "Received from " .
                $sender->full_name,

            "meta" => [

                "direction" =>
                    "credit",

                "sender_id" =>
                    $sender->id,

                "sender_name" =>
                    $sender->full_name,

                "sender_phone" =>
                    $sender->phone,

                "sender_wallet" =>
                    $senderWallet->wallet_number,

                "currency" =>
                    $recipientWallet->currency,

            ],

        ]);

        /*
        |--------------------------------------------------------------------------
        | Save Recipient
        |--------------------------------------------------------------------------
        |
        | This makes the recipient appear in the user's
        | saved/recent recipients next time.
        |
        */

        Recipient::firstOrCreate(

            [

                "user_id" =>
                    $sender->id,

                "wallet_number" =>
                    $recipientWallet->wallet_number,

            ],

            [

                "name" =>
                    $recipient->full_name,

                "phone" =>
                    $recipient->phone,

                "country" =>
                    $recipient->country,

                "currency" =>
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
        | Refresh Balances
        |--------------------------------------------------------------------------
        */

        $senderWallet->refresh();

        $recipientWallet->refresh();

        /*
        |--------------------------------------------------------------------------
        | Success Response
        |--------------------------------------------------------------------------
        */

        return response()->json([

            "success" =>
                true,

            "message" =>
                "Transfer completed successfully.",

            "data" => [

                "reference" =>
                    $reference,

                "transaction_id" =>
                    $transfer->id,

                "recipient" => [

                    "id" =>
                        $recipient->id,

                    "name" =>
                        $recipient->full_name,

                    "phone" =>
                        $recipient->phone,

                    "wallet_number" =>
                        $recipientWallet->wallet_number,

                    "country" =>
                        $recipient->country,

                    "currency" =>
                        $recipientWallet->currency,

                ],

                "amount" =>
                    $amount,

                "send_amount" =>
                    $amount,

                "receive_amount" =>
                    $amount,

                "fee" =>
                    $fee,

                "total" =>
                    $total,

                "exchange_rate" =>
                    1,

                "currency" =>
                    $senderWallet->currency,

                "status" =>
                    "completed",

                "remark" =>
                    $request->description,

                "wallet_balance" =>
                    (float) $senderWallet->balance,

                "created_at" =>
                    $transfer->created_at,

            ],

        ]);

    } catch (Throwable $e) {

        DB::rollBack();

        report($e);

        return response()->json([

            "success" =>
                false,

            "message" =>
                "Transfer failed.",

            "error" =>
                $e->getMessage(),

        ], 500);
    }
}
public function searchRecipient(Request $request)
{
    $request->validate([
        'query' => [
            'required',
            'string',
            'min:2',
        ],
    ]);

    $search = trim($request->input('query'));

    $user = User::with('wallet')
        ->where(function ($query) use ($search) {

            $query->where('phone', $search)
                ->orWhere('username', $search)
                ->orWhere('email', $search)
                ->orWhereHas('wallet', function ($walletQuery) use ($search) {

                    $walletQuery->where(
                        'wallet_number',
                        $search
                    );

                });

        })
        ->first();

    if (!$user) {

        return response()->json([
            'success' => false,
            'message' => 'Recipient not found.',
        ], 404);

    }

    if (!$user->wallet) {

        return response()->json([
            'success' => false,
            'message' => 'Recipient wallet not found.',
        ], 404);

    }

    if ($user->id === $request->user()->id) {

        return response()->json([
            'success' => false,
            'message' => 'You cannot transfer to yourself.',
        ], 422);

    }

    return response()->json([

        'success' => true,

        'message' => 'Recipient found.',

        'data' => [

            'id' => $user->id,

            'full_name' =>
                trim(
                    $user->first_name . ' ' .
                    $user->last_name
                ),

            'username' =>
                $user->username,

            'email' =>
                $user->email,

            'phone' =>
                $user->phone,

            'country' =>
                $user->country,

            'wallet_number' =>
                $user->wallet->wallet_number,

            'currency' =>
                $user->wallet->currency,

            'is_verified' =>
                (bool) $user->is_verified,

        ],

    ]);
}
public function verifyWalletRecipient(Request $request)
{
    $request->validate([
        'query' => [
            'required',
            'string',
        ],
    ]);

    $search = trim(
        $request->input('query')
    );

    $recipient = User::with('wallet')
        ->where(function ($query) use ($search) {

            $query->where('phone', $search)

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

    if (!$recipient) {

        return response()->json([

            'success' => false,

            'message' => 'Recipient not found.',

        ], 404);

    }

    if (!$recipient->wallet) {

        return response()->json([

            'success' => false,

            'message' => 'Recipient wallet not found.',

        ], 404);

    }

    if (
        $recipient->id ===
        $request->user()->id
    ) {

        return response()->json([

            'success' => false,

            'message' =>
                'You cannot transfer to yourself.',

        ], 422);

    }

    return response()->json([

        'success' => true,

        'message' =>
            'Recipient verified successfully.',

        'data' => [

            'id' =>
                $recipient->id,

            'full_name' =>
                $recipient->full_name,

            'username' =>
                $recipient->username,

            'email' =>
                $recipient->email,

            'phone' =>
                $recipient->phone,

            'wallet_number' =>
                $recipient->wallet->wallet_number,

            'country' =>
                $recipient->country,

            'currency' =>
                $recipient->wallet->currency,

            'is_verified' =>
                (bool) $recipient->is_verified,

        ],

    ]);
}
public function countries()
{
    $countries = Country::where(
        'is_active',
        true
    )
    ->orderBy('name')
    ->get();

    return response()->json([

        "success" => true,

        "message" => "Countries retrieved successfully.",

        "data" => $countries->map(function ($country) {

            return [

                "id" => $country->id,

                "name" => $country->name,

                "iso2" => $country->iso2,

                "iso3" => $country->iso3,

                "phone_code" => $country->phone_code,

                "currency" => $country->currency,

                "currency_symbol" => $country->currency_symbol,

                "exchange_rate" => (float) $country->exchange_rate,

                "flag" => $country->flag,

                "is_active" => (bool) $country->is_active,

            ];

        }),

    ]);
}
public function receipt($reference)
{
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
        auth()->id()
    )
    ->first();

    if (!$transfer) {

        return response()->json([

            "success" => false,

            "message" => "Transfer receipt not found."

        ],404);

    }

    return response()->json([

        "success" => true,

        "message" => "Transfer receipt retrieved successfully.",

        "data" => [

            "reference" => $transfer->reference,

            "status" => $transfer->status,

            "remark" => $transfer->remark,

            "send_amount" => $transfer->send_amount,

            "receive_amount" => $transfer->receive_amount,

            "fee" => $transfer->fee,

            "total" => $transfer->total,

            "exchange_rate" => $transfer->exchange_rate,

            "currency" => $transfer->destination_currency,

            "created_at" => $transfer->created_at,

            "sender" => [

                "id" => $transfer->user->id,

                "name" => $transfer->user->full_name,

                "email" => $transfer->user->email,

                "phone" => $transfer->user->phone,

                "wallet_number" => optional(
                    $transfer->user->wallet
                )->wallet_number,

            ],

            "recipient" => [

                "id" => $transfer->recipient->id,

                "name" => $transfer->recipient->full_name,

                "email" => $transfer->recipient->email,

                "phone" => $transfer->recipient->phone,

                "wallet_number" => optional(
                    $transfer->recipient->wallet
                )->wallet_number,

                "country" => $transfer->destination_country,

            ],

        ],

    ]);
}
public function quote(Request $request)
{
    $request->validate([

        "country_id" => [
            "required",
            "exists:countries,id",
        ],

        "amount" => [
            "required",
            "numeric",
            "min:1",
        ],

    ]);

    $country = Country::findOrFail(
        $request->country_id
    );

    /*
    |--------------------------------------------------------------------------
    | Exchange Rate
    |--------------------------------------------------------------------------
    */

    $exchangeRate = (float) $country->exchange_rate;

    /*
    |--------------------------------------------------------------------------
    | Transfer Fee
    |--------------------------------------------------------------------------
    */

    if ($request->amount <= 10000) {

        $fee = 10;

    } elseif ($request->amount <= 50000) {

        $fee = 25;

    } elseif ($request->amount <= 100000) {

        $fee = 50;

    } else {

        $fee = round(
            $request->amount * 0.005,
            2
        );

    }

    /*
    |--------------------------------------------------------------------------
    | Calculate
    |--------------------------------------------------------------------------
    */

    $sendAmount = (float) $request->amount;

    $receiveAmount = round(
        $sendAmount * $exchangeRate,
        2
    );

    $total = $sendAmount + $fee;

    return response()->json([

        "success" => true,

        "message" => "Transfer quote generated successfully.",

        "data" => [

            "country" => [

                "id" => $country->id,

                "name" => $country->name,

                "currency" => $country->currency,

                "currency_symbol" => $country->currency_symbol,

                "flag" => $country->flag,

            ],

            "exchange_rate" => $exchangeRate,

            "send_amount" => $sendAmount,

            "receive_amount" => $receiveAmount,

            "fee" => $fee,

            "total" => $total,

        ],

    ]);
}
public function history(Request $request)
{
    $query = Transfer::where(
        'user_id',
        $request->user()->id
    );

    /*
    |--------------------------------------------------------------------------
    | Search by Reference
    |--------------------------------------------------------------------------
    */

    if ($request->filled('reference')) {

        $query->where(
            'reference',
            'like',
            '%'.$request->reference.'%'
        );

    }

    /*
    |--------------------------------------------------------------------------
    | Filter by Status
    |--------------------------------------------------------------------------
    */

    if ($request->filled('status')) {

        $query->where(
            'status',
            $request->status
        );

    }

    /*
    |--------------------------------------------------------------------------
    | Filter by Date
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Sorting
    |--------------------------------------------------------------------------
    */

    $query->latest();

    /*
    |--------------------------------------------------------------------------
    | Paginate
    |--------------------------------------------------------------------------
    */

    $transfers = $query->paginate(
        $request->get('per_page', 20)
    );

    /*
    |--------------------------------------------------------------------------
    | Summary
    |--------------------------------------------------------------------------
    */

    $summary = [

        "total_transfers" => Transfer::where(
            'user_id',
            $request->user()->id
        )->count(),

        "total_amount" => Transfer::where(
            'user_id',
            $request->user()->id
        )->sum('send_amount'),

        "completed" => Transfer::where(
            'user_id',
            $request->user()->id
        )->where(
            'status',
            'completed'
        )->count(),

        "pending" => Transfer::where(
            'user_id',
            $request->user()->id
        )->where(
            'status',
            'pending'
        )->count(),

        "failed" => Transfer::where(
            'user_id',
            $request->user()->id
        )->where(
            'status',
            'failed'
        )->count(),

    ];

    return response()->json([

        "success" => true,

        "summary" => $summary,

        "data" => $transfers,

    ]);
}
public function recipients(Request $request)
{
    $recipients = Recipient::where(
        'user_id',
        $request->user()->id
    )
    ->latest()
    ->get();

    return response()->json([
        "success" => true,
        "message" => "Recipients retrieved successfully.",
        "data" => $recipients,
    ]);
}


public function verify(
    VerifyRecipientRequest $request
)
{
    return $this->transferService
        ->verifyRecipient(
            $request->validated()
        );
}

}