<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Data\DataPurchaseRequest;
use App\Models\DataPurchase;

use App\Services\Data\DataPurchaseService;

use App\Services\Reloadly\Countries\ReloadlyCountryService;
use App\Services\Reloadly\Operators\ReloadlyOperatorService;
use App\Services\Reloadly\Products\ReloadlyProductService;
use App\Services\Reloadly\Quotes\ReloadlyQuoteService;

use Illuminate\Http\Request;
use Throwable;

class DataController extends Controller
{
    public function __construct(

        private readonly ReloadlyCountryService $countries,

        private readonly ReloadlyOperatorService $operators,

        private readonly ReloadlyProductService $products,

        private readonly ReloadlyQuoteService $quotes,

        private readonly DataPurchaseService $purchaseService,

    ) {}

    /*
    |--------------------------------------------------------------------------
    | Countries
    |--------------------------------------------------------------------------
    */
public function countries()
{
    try {

        return response()->json([

            'success' => true,

            'message' => 'Countries loaded successfully.',

            'data' => $this->countries->all(),

        ]);

    } catch (Throwable $e) {

        return response()->json([

            'success' => false,

            'message' => $e->getMessage(),

        ],500);

    }
}
    /*
    |--------------------------------------------------------------------------
    | Networks / Operators
    |--------------------------------------------------------------------------
    */

    public function networks(
        string $country
    )
    {
        try {

            return response()->json([

                'success' => true,

                'data' => $this->operators->all(
                    $country
                ),

            ]);

        } catch (Throwable $e) {

            return response()->json([

                'success' => false,

                'message' => $e->getMessage(),

            ], 500);

        }
    }

    /*
    |--------------------------------------------------------------------------
    | Bundles / Products
    |--------------------------------------------------------------------------
    */

    public function bundles(
        int $network
    )
    {
        try {

            return response()->json([

                'success' => true,

                'data' => $this->products->all(
                    $network
                ),

            ]);

        } catch (Throwable $e) {

            return response()->json([

                'success' => false,

                'message' => $e->getMessage(),

            ], 500);

        }
    }
    /*
    |--------------------------------------------------------------------------
    | Quote
    |--------------------------------------------------------------------------
    */

    public function quote(Request $request)
    {
        $request->validate([

            'country' => ['required'],

            'operator' => ['required'],

            'product' => ['required'],

            'recipient' => ['required'],

            'amount' => ['required', 'numeric'],

        ]);

        try {

            $quote = $this->quotes->quote([

                'country' => $request->country,

                'operator' => $request->operator,

                'product' => $request->product,

                'recipient' => $request->recipient,

                'amount' => (float) $request->amount,

            ]);

            return response()->json([

                'success' => true,

                'message' => 'Quote generated successfully.',

                'data' => $quote,

            ]);

        } catch (Throwable $e) {

            return response()->json([

                'success' => false,

                'message' => $e->getMessage(),

            ], 422);

        }
    }

    /*
    |--------------------------------------------------------------------------
    | Purchase
    |--------------------------------------------------------------------------
    */
public function purchase(
    DataPurchaseRequest $request
) {
    try {

        $result = $this->purchaseService->purchase(
            $request->user(),
            [
                'country' => $request->country_code,

                'operator' => $request->network_id,

                'product' => $request->bundle_id,

                'recipient' => $request->phone,

                'pin' => $request->pin,

                'network_name' => $request->network_name,

                'bundle_name' => $request->bundle_name,
            ]
        );

        return response()->json([

            'success' => true,

            'message' =>
                'Data bundle purchased successfully.',

            'data' => $result,

        ]);

    } catch (Throwable $e) {

        return response()->json([

            'success' => false,

            'message' => $e->getMessage(),

        ], 422);

    }
}
    /*
    |--------------------------------------------------------------------------
    | History
    |--------------------------------------------------------------------------
    */

    public function history(Request $request)
    {
        try {

            $history = DataPurchase::where(
                'user_id',
                $request->user()->id
            )
            ->latest()
            ->paginate(20);

            return response()->json([

                'success' => true,

                'message' => 'Purchase history loaded successfully.',

                'data' => $history,

            ]);

        } catch (Throwable $e) {

            return response()->json([

                'success' => false,

                'message' => $e->getMessage(),

            ], 500);

        }
    }

    /*
    |--------------------------------------------------------------------------
    | Receipt
    |--------------------------------------------------------------------------
    */

    public function receipt(
        Request $request,
        string $reference
    )
    {
        try {

            $purchase = DataPurchase::with([
                'user.wallet',
            ])

            ->where(
                'user_id',
                $request->user()->id
            )

            ->where(
                'reference',
                $reference
            )

            ->firstOrFail();

            return response()->json([

                'success' => true,

                'message' => 'Receipt loaded successfully.',

                'data' => [

                    'reference' =>
                        $purchase->reference,

                    'status' =>
                        $purchase->status,

                    'date' =>
                        optional(
                            $purchase->created_at
                        )->format('d M Y H:i'),

                    'amount' =>
                        (double) $purchase->amount,

                    'fee' => 0,

                    'total' =>
                        (double) $purchase->amount,

                    'currency' =>
                        $purchase->currency,

                    'description' =>
                        'Data Bundle Purchase',

                    'network' =>
                        $purchase->network,

                    'bundle' =>
                        $purchase->bundle,

                    'provider' =>
                        $purchase->provider,

                    'provider_reference' =>
                        $purchase->provider_reference,

                    'recipient' => [

                        'phone' =>
                            $purchase->phone,

                    ],

                    'sender' => [

                        'id' =>
                            $purchase->user->id,

                        'name' => trim(

                            $purchase->user->first_name .

                            ' ' .

                            $purchase->user->last_name

                        ),

                        'phone' =>
                            $purchase->user->phone,

                        'wallet_number' =>
                            optional(
                                $purchase->user->wallet
                            )->wallet_number,

                    ],

                ],

            ]);

        } catch (Throwable $e) {

            return response()->json([

                'success' => false,

                'message' => $e->getMessage(),

            ], 404);

        }
    }
    /*
    |--------------------------------------------------------------------------
    | Beneficiaries
    |--------------------------------------------------------------------------
    */

    public function beneficiaries(Request $request)
    {
        try {

            $beneficiaries = DataPurchase::where(
                'user_id',
                $request->user()->id
            )

            ->select(
                'phone',
                'network'
            )

            ->distinct()

            ->latest()

            ->take(20)

            ->get()

            ->values()

            ->map(function ($item, $index) {

                return [

                    'id' => $index + 1,

                    'name' => $item->phone,

                    'phone' => $item->phone,

                    'network' => $item->network,

                ];

            });

            return response()->json([

                'success' => true,

                'message' => 'Beneficiaries loaded successfully.',

                'data' => $beneficiaries,

            ]);

        } catch (Throwable $e) {

            return response()->json([

                'success' => false,

                'message' => $e->getMessage(),

            ], 500);

        }
    }
}