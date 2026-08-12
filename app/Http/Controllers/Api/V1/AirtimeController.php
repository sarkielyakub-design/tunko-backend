<?php

namespace App\Http\Controllers\Api\V1;
use Illuminate\Support\Facades\Log;
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
use App\Services\Reloadly\Countries\ReloadlyCountryService;
use App\Services\Reloadly\Operators\ReloadlyOperatorService;


class AirtimeController extends Controller
{
 
    public function purchase(PurchaseRequest $request)
{
    $user = $request->user();

    /*
    |--------------------------------------------------------------------------
    | Verify Transaction PIN
    |--------------------------------------------------------------------------
    */

    if (
        empty($user->transaction_pin) ||
        !Hash::check(
            $request->pin,
            $user->transaction_pin
        )
    ) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid transaction PIN.',
        ], 422);
    }

    /*
    |--------------------------------------------------------------------------
    | Verify Wallet
    |--------------------------------------------------------------------------
    */

    $user->load('wallet');

    if (!$user->wallet) {
        return response()->json([
            'success' => false,
            'message' => 'Wallet not found.',
        ], 404);
    }

    /*
    |--------------------------------------------------------------------------
    | Wallet Active
    |--------------------------------------------------------------------------
    */

    if (!$user->wallet->is_active) {
        return response()->json([
            'success' => false,
            'message' => 'Wallet is inactive.',
        ], 422);
    }

    /*
    |--------------------------------------------------------------------------
    | Check Balance
    |--------------------------------------------------------------------------
    */

    if (
        $user->wallet->balance <
        $request->amount
    ) {
        return response()->json([
            'success' => false,
            'message' => 'Insufficient wallet balance.',
        ], 422);
    }

    DB::beginTransaction();

    try {

        /*
        |--------------------------------------------------------------------------
        | Generate Reference
        |--------------------------------------------------------------------------
        */

        $reference =
            'AIR' .
            strtoupper(
                Str::random(12)
            );

        /*
        |--------------------------------------------------------------------------
        | Purchase From Reloadly
        |--------------------------------------------------------------------------
        */

        $provider =
            $this->airtimeService->purchase([

                'reference' =>
                    $reference,

                'operator_id' =>
                    $request->operator_id,

                'country_code' =>
                    $request->country_code,

                'phone' =>
                    $request->phone,

                'amount' =>
                    (float) $request->amount,

            ]);

        if (
            empty($provider['success'])
        ) {
            throw new \Exception(
                $provider['message']
                ?? 'Airtime purchase failed.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Debit Wallet
        |--------------------------------------------------------------------------
        */

        $user->wallet->decrement(
            'balance',
            $request->amount
        );

        /*
        |--------------------------------------------------------------------------
        | Save Airtime
        |--------------------------------------------------------------------------
        */

        $airtime = Airtime::create([

            'user_id' =>
                $user->id,

            'reference' =>
                $reference,

            /*
             * IMPORTANT:
             * This is ISO code, not countries.id.
             */
            'country_id' =>
                $request->country_code,

            'country' =>
                $request->country,

            'network' =>
                $request->network,

            'phone' =>
                $request->phone,

            'amount' =>
                $request->amount,

            'currency' =>
                $user->wallet->currency,

            'provider' =>
                'Reloadly',

            'provider_reference' =>
                $provider['provider_reference'] ?? null,

            'status' =>
                $provider['status'] ?? 'completed',

        ]);

        /*
        |--------------------------------------------------------------------------
        | Save Transaction
        |--------------------------------------------------------------------------
        */

        Transaction::create([

            'user_id' =>
                $user->id,

            'reference' =>
                $reference,

            'type' =>
                'airtime',

            'title' =>
                'Airtime Purchase',

            'description' =>
                'Airtime for ' .
                $request->phone,

            'amount' =>
                $request->amount,

            'currency' =>
                $user->wallet->currency,

            'fee' =>
                0,

            'total' =>
                $request->amount,

            'status' =>
                $provider['status'] ?? 'completed',

            'meta' => [

                'country_code' =>
                    $request->country_code,

                'operator_id' =>
                    $request->operator_id,

                'network' =>
                    $request->network,

                'phone' =>
                    $request->phone,

                'provider' =>
                    'Reloadly',

                'provider_reference' =>
                    $provider['provider_reference']
                    ?? null,

            ],

        ]);

        DB::commit();

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([

            'success' =>
                true,

            'message' =>
                'Airtime purchased successfully.',

            'data' => [

                'id' =>
                    $airtime->id,

                'reference' =>
                    $reference,

                'provider_reference' =>
                    $provider['provider_reference']
                    ?? null,

                'phone' =>
                    $request->phone,

                'country_code' =>
                    $request->country_code,

                'country' =>
                    $request->country,

                'network' =>
                    $request->network,

                'amount' =>
                    (float) $request->amount,

                'currency' =>
                    $user->wallet->currency,

                'status' =>
                    $provider['status']
                    ?? 'completed',

                'wallet_balance' =>
                    $user->wallet
                        ->fresh()
                        ->balance,

                'created_at' =>
                    $airtime->created_at,

            ],

        ]);

    } catch (Throwable $e) {

        DB::rollBack();

        Log::error(
            'Airtime purchase failed',
            [
                'user_id' =>
                    $user->id,

                'country_code' =>
                    $request->country_code,

                'operator_id' =>
                    $request->operator_id,

                'phone' =>
                    $request->phone,

                'amount' =>
                    $request->amount,

                'error' =>
                    $e->getMessage(),
            ]
        );

        return response()->json([

            'success' =>
                false,

            'message' =>
                $e->getMessage(),

        ], 500);
    }
}
public function __construct(
    private AirtimeService $airtimeService,
    private ReloadlyCountryService $countries,
    private ReloadlyOperatorService $operators,
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
    try {

        return response()->json([

            "success" => true,

            "message" => "Countries loaded successfully.",

            "data" => $this->countries->all(),

        ]);

    } catch (\Throwable $e) {

        Log::error($e);

        return response()->json([

            "success" => false,

            "message" => $e->getMessage(),

        ], 500);

    }
}
/**

*/
public function networks(string $country)
{
    try {

        return response()->json([

            "success" => true,

            "message" => "Networks loaded successfully.",

            "data" => $this->operators->airtime($country),

        ]);

    } catch (\Throwable $e) {

        Log::error($e);

        return response()->json([

            "success" => false,

            "message" => $e->getMessage(),

        ], 500);

    }
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
public function operators(int $countryId): Response
{
    return $this->http->get(
        "/operators/countries/{$countryId}"
    );
}
}
