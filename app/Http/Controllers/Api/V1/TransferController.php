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

    if (!Hash::check($request->pin, $sender->transaction_pin)) {

        return response()->json([
            "success" => false,
            "message" => "Invalid transaction PIN."
        ], 422);

    }

    /*
    |--------------------------------------------------------------------------
    | Verify Sender Wallet
    |--------------------------------------------------------------------------
    */

    if (!$sender->wallet) {

        return response()->json([
            "success" => false,
            "message" => "Wallet not found."
        ], 404);

    }

    /*
    |--------------------------------------------------------------------------
    | Verify Recipient
    |--------------------------------------------------------------------------
    */

    $recipient = User::with('wallet')->find(
        $request->recipient_id
    );

    if (!$recipient) {

        return response()->json([
            "success" => false,
            "message" => "Recipient not found."
        ], 404);

    }

    if (!$recipient->wallet) {

        return response()->json([
            "success" => false,
            "message" => "Recipient wallet unavailable."
        ], 404);

    }

    /*
    |--------------------------------------------------------------------------
    | Prevent Self Transfer
    |--------------------------------------------------------------------------
    */

    if ($sender->id === $recipient->id) {

        return response()->json([
            "success" => false,
            "message" => "You cannot transfer to yourself."
        ], 422);

    }

    /*
    |--------------------------------------------------------------------------
    | Calculate Fee
    |--------------------------------------------------------------------------
    */

    $fee = $request->amount <= 10000
        ? 10
        : ($request->amount <= 50000
            ? 25
            : 50);

    $total = $request->amount + $fee;

    /*
    |--------------------------------------------------------------------------
    | Check Wallet Balance
    |--------------------------------------------------------------------------
    */

    if ($sender->wallet->balance < $total) {

        return response()->json([
            "success" => false,
            "message" => "Insufficient wallet balance."
        ], 422);

    }

    DB::beginTransaction();

    try {

        $reference = "TNK" . strtoupper(Str::random(12));

        /*
        |--------------------------------------------------------------------------
        | Debit Sender Wallet
        |--------------------------------------------------------------------------
        */

        $sender->wallet->decrement(
            'balance',
            $total
        );

        /*
        |--------------------------------------------------------------------------
        | Credit Recipient Wallet
        |--------------------------------------------------------------------------
        */

        $recipient->wallet->increment(
            'balance',
            $request->amount
        );
/*
|--------------------------------------------------------------------------
| Save Transfer
|--------------------------------------------------------------------------
*/

$transfer = Transfer::create([

    "user_id" => $sender->id,

    "recipient_id" => $recipient->id,

    "reference" => $reference,

    "destination_country" => $recipient->country ?? "Nigeria",

    "destination_currency" => $recipient->wallet->currency,

    "send_amount" => $request->amount,

    "receive_amount" => $request->amount,

    "exchange_rate" => 1,

    "fee" => $fee,

    "total" => $total,

    "status" => "completed",

    "remark" => $request->remark,

]);

/*
|--------------------------------------------------------------------------
| Sender Transaction
|--------------------------------------------------------------------------
*/

Transaction::create([

    "user_id" => $sender->id,

    "reference" => $reference,

    "type" => "wallet_transfer",

    "title" => "Wallet Transfer",

    "description" => "Transfer to ".$recipient->full_name,

    "amount" => $request->amount,

    "currency" => $sender->wallet->currency,

    "fee" => $fee,

    "total" => $total,

    "status" => "completed",

    "recipient" => $recipient->full_name,

    "sender" => $sender->full_name,

]);

/*
|--------------------------------------------------------------------------
| Recipient Transaction
|--------------------------------------------------------------------------
*/

Transaction::create([

    "user_id" => $recipient->id,

    "reference" => $reference,

    "type" => "wallet_received",

    "title" => "Wallet Credit",

    "description" => "Received from ".$sender->full_name,

    "amount" => $request->amount,

    "currency" => $recipient->wallet->currency,

    "fee" => 0,

    "total" => $request->amount,

    "status" => "completed",

    "recipient" => $recipient->full_name,

    "sender" => $sender->full_name,

]);

/*
|--------------------------------------------------------------------------
| Save Recipient
|--------------------------------------------------------------------------
*/

Recipient::firstOrCreate(

    [

        "user_id" => $sender->id,

        "wallet_number" => $recipient->wallet->wallet_number,

    ],

    [

        "name" => $recipient->full_name,

        "phone" => $recipient->phone,

        "country" => $recipient->country,

        "currency" => $recipient->wallet->currency,

    ]

);

DB::commit();

return response()->json([

    "success" => true,

    "message" => "Transfer completed successfully.",

    "data" => [

        "reference" => $reference,

        "recipient" => [

            "id" => $recipient->id,

            "name" => $recipient->full_name,

            "phone" => $recipient->phone,

            "wallet_number" => $recipient->wallet->wallet_number,

        ],

        "amount" => $request->amount,

        "fee" => $fee,

        "total" => $total,

        "currency" => $sender->wallet->currency,

        "status" => "completed",

        "wallet_balance" => $sender
            ->wallet
            ->fresh()
            ->balance,

        "created_at" => $transfer->created_at,

    ],

]);
        } catch (Throwable $e) {

            DB::rollBack();

            return response()->json([

                "success"=>false,

                "message"=>"Transfer failed.",

                "error"=>$e->getMessage()

            ],500);

        }
        

    }
    
public function searchRecipient(Request $request)
{
    $request->validate([
        'query' => [
            'required',
            'string',
        ],
    ]);

    $search = trim($request->input('query'));

    $user = User::with('wallet')
        ->where('phone', $search)
        ->orWhere('username', $search)
        ->orWhere('email', $search)
        ->orWhereHas('wallet', function ($q) use ($search) {
            $q->where('wallet_number', $search);
        })
        ->first();

    if (!$user) {
        return response()->json([
            "success" => false,
            "message" => "Recipient not found."
        ], 404);
    }

    if (!$user->wallet) {
        return response()->json([
            "success" => false,
            "message" => "Recipient wallet not found."
        ], 404);
    }

    if ($user->id === auth()->id()) {
        return response()->json([
            "success" => false,
            "message" => "You cannot transfer to yourself."
        ], 422);
    }

    return response()->json([
        "success" => true,
        "message" => "Recipient found.",
        "recipient" => [
            "id" => $user->id,
            "full_name" => $user->first_name . " " . $user->last_name,
            "username" => $user->username,
            "email" => $user->email,
            "phone" => $user->phone,
            "country" => $user->country,
            "wallet_number" => $user->wallet->wallet_number,
            "currency" => $user->wallet->currency,
            "is_verified" => (bool) $user->is_verified,
        ],
    ]);
}
public function verifyWalletRecipient(Request $request)
{
    $request->validate([
        "query" => [
            "required",
            "string",
        ],
    ]);

    $recipient = User::with('wallet')

        ->where('phone', $request->query)

        ->orWhere('username', $request->query)

        ->orWhere('email', $request->query)

        ->orWhereHas('wallet', function ($query) use ($request) {

            $query->where(
                'wallet_number',
                $request->query
            );

        })

        ->first();

    if (!$recipient) {

        return response()->json([

            "success" => false,

            "message" => "Recipient not found."

        ], 404);

    }

    if (!$recipient->wallet) {

        return response()->json([

            "success" => false,

            "message" => "Recipient wallet not found."

        ], 404);

    }

    if ($recipient->id === auth()->id()) {

        return response()->json([

            "success" => false,

            "message" => "You cannot transfer to yourself."

        ], 422);

    }

    return response()->json([

        "success" => true,

        "message" => "Recipient verified successfully.",

        "data" => [

            "id" => $recipient->id,

            "full_name" => $recipient->full_name,

            "username" => $recipient->username,

            "email" => $recipient->email,

            "phone" => $recipient->phone,

            "wallet_number" => $recipient->wallet->wallet_number,

            "country" => $recipient->country,

            "currency" => $recipient->wallet->currency,

            "is_verified" => (bool) $recipient->is_verified,

        ]

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