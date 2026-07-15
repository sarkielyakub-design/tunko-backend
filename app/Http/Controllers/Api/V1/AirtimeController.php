<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Airtime;
use App\Models\Transaction;
use App\Services\AirtimeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Country;
use App\Models\Network;
use App\Http\Requests\Airtime\PurchaseRequest;


class AirtimeController extends Controller
{
    //
    public function purchase(PurchaseRequest $request)
{
    $user = $request->user();

    /*
    |--------------------------------------------------------------------------
    | Verify Transaction PIN
    |--------------------------------------------------------------------------
    */

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
    | Verify Wallet
    |--------------------------------------------------------------------------
    */

    if (!$user->wallet) {
        return response()->json([
            "success" => false,
            "message" => "Wallet not found."
        ], 404);
    }

    /*
    |--------------------------------------------------------------------------
    | Check Balance
    |--------------------------------------------------------------------------
    */

    if ($user->wallet->balance < $request->amount) {
        return response()->json([
            "success" => false,
            "message" => "Insufficient wallet balance."
        ], 422);
    }

    DB::beginTransaction();

    try {

        $reference = "AIR".strtoupper(Str::random(12));

        /*
        |--------------------------------------------------------------------------
        | Provider Purchase
        |--------------------------------------------------------------------------
        */

        $provider = $this->airtimeService->purchase(
            $request->validated()
        );

        if (!$provider["success"]) {

            DB::rollBack();

            return response()->json([
                "success" => false,
                "message" => $provider["message"]
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Debit Wallet
        |--------------------------------------------------------------------------
        */

        $user->wallet->decrement(
            "balance",
            $request->amount
        );

        /*
        |--------------------------------------------------------------------------
        | Save Airtime
        |--------------------------------------------------------------------------
        */

        $airtime = Airtime::create([

            "user_id" => $user->id,

            "reference" => $reference,

            "country_id" => $request->country_id,

            "country" => $request->country,

            "network" => $request->network,

            "phone" => $request->phone,

            "amount" => $request->amount,

            "currency" => $user->wallet->currency,

            "provider" => "Reloadly",

            "provider_reference" =>
                $provider["provider_reference"],

            "status" => "completed",

        ]);

        /*
        |--------------------------------------------------------------------------
        | Transaction
        |--------------------------------------------------------------------------
        */

        Transaction::create([

            "user_id" => $user->id,

            "reference" => $reference,

            "type" => "airtime",

            "title" => "Airtime Purchase",

            "description" =>
                "Airtime for ".$request->phone,

            "amount" => $request->amount,

            "currency" => $user->wallet->currency,

            "fee" => 0,

            "total" => $request->amount,

            "status" => "completed",

        ]);

        DB::commit();

        return response()->json([

            "success" => true,

            "message" => "Airtime purchased successfully.",

            "data" => [

                "reference" => $reference,

                "phone" => $request->phone,

                "amount" => $request->amount,

                "network" => $request->network,

                "status" => "completed",

            ]

        ]);

    } catch (\Throwable $e) {

        DB::rollBack();

        return response()->json([

            "success" => false,

            "message" => $e->getMessage(),

        ],500);

    }
}
public function __construct(
    private AirtimeService $airtimeService
) {}
public function history(Request $request)
{
    return response()->json([

        "success" => true,

        "data" => Airtime::where(
            "user_id",
            $request->user()->id
        )
        ->latest()
        ->paginate(20),

    ]);
}
public function receipt($reference)
{
    $airtime = Airtime::where(
        "reference",
        $reference
    )->firstOrFail();

    return response()->json([

        "success" => true,

        "data" => $airtime,

    ]);
}
/**

*/
public function countries()
{
    return response()->json([
        "success" => true,
        "data" => Country::where('is_active', true)
            ->orderBy('name')
            ->get(),
    ]);
}

/**

*/
public function networks($country)
{
    return response()->json([
        "success" => true,
        "data" => Network::where(
                'country_id',
                $country
            )
            ->where('supports_airtime', true)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(),
    ]);
}
public function quote(Request $request)
{
    $request->validate([
        'amount' => 'required|numeric|min:50',
    ]);

    return response()->json([
        "success" => true,
        "data" => [
            "amount" => $request->amount,
            "fee" => 0,
            "total" => $request->amount,
            "currency" => "NGN",
        ]
    ]);
}
public function beneficiaries(Request $request)
{
    $items = Airtime::where(
        'user_id',
        $request->user()->id
    )
    ->latest()
    ->select(
        'phone',
        'network'
    )
    ->distinct('phone')
    ->take(10)
    ->get();

    return response()->json([
        "success" => true,
        "data" => $items,
    ]);
}
}
