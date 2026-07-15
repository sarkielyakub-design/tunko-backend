<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Data\DataPurchaseRequest;
use App\Models\Country;
use App\Models\DataPurchase;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Throwable;

class DataController extends Controller
{
    /**
     * Countries
     */
    public function countries()
{
    return response()->json([

        "success" => true,

        "data" => Country::where(
            'is_active',
            true
        )
        ->orderBy('name')
        ->get(),

    ]);
}

    /**
     * Networks
     */
    public function networks($country)
{
    return response()->json([

        "success" => true,

        "data" => [

            [

                "id" => 1,

                "name" => "Airtel",

                "logo" => "",

            ],

            [

                "id" => 2,

                "name" => "Moov Africa",

                "logo" => "",

            ],

        ]

    ]);
}
    /**
     * Bundles
     */
  public function bundles($network)
{
    return response()->json([

        "success" => true,

        "data" => [

            [

                "id" => 1,

                "name" => "1 GB",

                "volume" => "1 GB",

                "amount" => 1000,

                "validity" => "1 Day",

            ],

            [

                "id" => 2,

                "name" => "2 GB",

                "volume" => "2 GB",

                "amount" => 2000,

                "validity" => "7 Days",

            ],

            [

                "id" => 3,

                "name" => "5 GB",

                "volume" => "5 GB",

                "amount" => 5000,

                "validity" => "30 Days",

            ],

        ]

    ]);
}

    /**
     * Purchase
     */
    public function purchase(DataPurchaseRequest $request)
{
    $user = $request->user();

    /*
    |--------------------------------------------------------------------------
    | Verify Transaction PIN
    |--------------------------------------------------------------------------
    */

    if (!$user->transaction_pin) {

        return response()->json([
            "success" => false,
            "message" => "Please create your transaction PIN first."
        ], 422);

    }

    if (!Hash::check(
        $request->pin,
        $user->transaction_pin
    )) {

        return response()->json([
            "success" => false,
            "message" => "Invalid transaction PIN."
        ], 422);

    }

    /*
    |--------------------------------------------------------------------------
    | Wallet
    |--------------------------------------------------------------------------
    */

    $wallet = $user->wallet;

    if (!$wallet) {

        return response()->json([
            "success" => false,
            "message" => "Wallet not found."
        ], 404);

    }

    /*
    |--------------------------------------------------------------------------
    | Demo Bundle (Replace with Provider/API later)
    |--------------------------------------------------------------------------
    */

    $bundles = [

        1 => [
            "name" => "1 GB",
            "amount" => 1000,
            "validity" => "1 Day",
        ],

        2 => [
            "name" => "2 GB",
            "amount" => 2000,
            "validity" => "7 Days",
        ],

        3 => [
            "name" => "5 GB",
            "amount" => 5000,
            "validity" => "30 Days",
        ],

    ];

    if (!isset($bundles[$request->bundle_id])) {

        return response()->json([
            "success" => false,
            "message" => "Invalid bundle selected."
        ], 422);

    }

    $bundle = $bundles[$request->bundle_id];

    $amount = $bundle["amount"];

    /*
    |--------------------------------------------------------------------------
    | Check Wallet Balance
    |--------------------------------------------------------------------------
    */

    if ($wallet->balance < $amount) {

        return response()->json([
            "success" => false,
            "message" => "Insufficient wallet balance."
        ], 422);

    }

    DB::beginTransaction();

    try {

        /*
        |--------------------------------------------------------------------------
        | Debit Wallet
        |--------------------------------------------------------------------------
        */

        $wallet->decrement(
            "balance",
            $amount
        );

        /*
        |--------------------------------------------------------------------------
        | Reference
        |--------------------------------------------------------------------------
        */

        $reference = "DAT" .
            strtoupper(
                Str::random(12)
            );

        /*
        |--------------------------------------------------------------------------
        | TODO
        | Call Provider API
        |--------------------------------------------------------------------------
        */

        $providerReference = null;

        /*
        |--------------------------------------------------------------------------
        | Save Purchase
        |--------------------------------------------------------------------------
        */

        $purchase = DataPurchase::create([

            "user_id" => $user->id,

            "country_id" => $request->country_id,

            "reference" => $reference,

            "network_id" => $request->network_id,

            "bundle_id" => $request->bundle_id,

            "phone" => $request->phone,

            "network" => "Airtel",

            "bundle" => $bundle["name"],

            "amount" => $amount,

            "currency" => $wallet->currency,

            "provider" => "Mock",

            "provider_reference" => $providerReference,

            "provider_response" => null,

            "status" => "completed",

        ]);

        /*
        |--------------------------------------------------------------------------
        | Save Transaction
        |--------------------------------------------------------------------------
        */

        Transaction::create([

            "user_id" => $user->id,

            "reference" => $reference,

            "type" => "data",

            "title" => "Data Bundle",

            "description" =>
                $bundle["name"] .
                " purchased for " .
                $request->phone,

            "amount" => $amount,

            "currency" => $wallet->currency,

            "status" => "completed",

        ]);

        DB::commit();

        return response()->json([

            "success" => true,

            "message" => "Data bundle purchased successfully.",

            "data" => [

                "reference" => $purchase->reference,

                "phone" => $purchase->phone,

                "network" => $purchase->network,

                "bundle" => $purchase->bundle,

                "amount" => $purchase->amount,

                "currency" => $purchase->currency,

                "status" => $purchase->status,

                "created_at" => $purchase->created_at,

            ]

        ]);

    } catch (Throwable $e) {

        DB::rollBack();

        return response()->json([

            "success" => false,

            "message" => "Data purchase failed.",

            "error" => config('app.debug')
                ? $e->getMessage()
                : null,

        ], 500);

    }
}

    /**
     * History
     */
    /**
 * Data Purchase History
 */
public function history(Request $request)
{
    $history = DataPurchase::where(
        'user_id',
        $request->user()->id
    )
    ->latest()
    ->get()
    ->map(function ($purchase) {

        return [

            "reference" => $purchase->reference,

            "phone" => $purchase->phone,

            "network" => $purchase->network,

            "bundle" => $purchase->bundle,

            "amount" => $purchase->amount,

            "currency" => $purchase->currency,

            "status" => $purchase->status,

            "created_at" => $purchase->created_at,

        ];

    });

    return response()->json([

        "success" => true,

        "data" => $history,

    ]);
}
    /**
     * Receipt
     */
   /**
 * Receipt
 */
public function receipt(
    Request $request,
    string $reference
)
{
    $purchase = DataPurchase::where(
        'user_id',
        $request->user()->id
    )
    ->where(
        'reference',
        $reference
    )
    ->first();

    if (!$purchase) {

        return response()->json([

            "success" => false,

            "message" => "Receipt not found.",

        ],404);

    }

    return response()->json([

        "success" => true,

        "data" => [

            "reference" => $purchase->reference,

            "phone" => $purchase->phone,

            "network" => $purchase->network,

            "bundle" => $purchase->bundle,

            "amount" => $purchase->amount,

            "currency" => $purchase->currency,

            "status" => $purchase->status,

            "provider" => $purchase->provider,

            "provider_reference" =>
                $purchase->provider_reference,

            "created_at" =>
                $purchase->created_at,

        ],

    ]);
}
public function quote(Request $request)
{
    $request->validate([
        'bundle_id' => 'required|integer',
        'phone' => 'required|string',
    ]);

    $bundles = [
        1 => [
            "name" => "1 GB",
            "amount" => 1000,
        ],
        2 => [
            "name" => "2 GB",
            "amount" => 2000,
        ],
        3 => [
            "name" => "5 GB",
            "amount" => 5000,
        ],
    ];

    if (!isset($bundles[$request->bundle_id])) {
        return response()->json([
            "success" => false,
            "message" => "Bundle not found.",
        ],404);
    }

    $bundle = $bundles[$request->bundle_id];

    return response()->json([
        "success" => true,
        "data" => [
            "amount" => $bundle["amount"],
            "fee" => 0,
            "total" => $bundle["amount"],
            "currency" => "NGN",
        ]
    ]);
}
public function beneficiaries(Request $request)
{
    $items = DataPurchase::where(
        'user_id',
        $request->user()->id
    )
    ->select(
        'phone',
        'network'
    )
    ->distinct()
    ->latest()
    ->take(10)
    ->get()
    ->map(function ($item, $index) {

        return [

            "id" => $index + 1,

            "name" => $item->phone,

            "phone" => $item->phone,

            "network" => $item->network,

        ];

    });

    return response()->json([

        "success" => true,

        "data" => $items,

    ]);
}

    
}