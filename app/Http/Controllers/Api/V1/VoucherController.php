<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Services\Voucher\VoucherPurchaseService;
use Illuminate\Http\Request;
use Throwable;

class VoucherController extends Controller
{
    public function __construct(
        private readonly VoucherPurchaseService $purchaseService
    ) {}

    /*
    |--------------------------------------------------------------------------
    | AVAILABLE VOUCHER PRODUCTS
    |--------------------------------------------------------------------------
    |
    | Returns only products that currently have inventory.
    |
    */

    public function products(Request $request)
    {
        try {

            $query = Voucher::query()
                ->where('status', 'available')
                ->whereIn(
                    'country_code',
                    [
                        'NE', // Niger
                        'TD', // Chad
                    ]
                )
                ->where(function ($query) {

                    $query->whereNull('expires_at')
                        ->orWhere(
                            'expires_at',
                            '>',
                            now()
                        );
                });

            /*
            |--------------------------------------------------------------------------
            | Country
            |--------------------------------------------------------------------------
            */

            if ($request->filled('country_code')) {

                $countryCode = strtoupper(
                    $request->country_code
                );

                if (!in_array(
                    $countryCode,
                    ['NE', 'TD'],
                    true
                )) {
                    return response()->json([
                        'success' => false,
                        'message' =>
                            'Voucher service is available only for Niger and Chad.',
                    ], 422);
                }

                $query->where(
                    'country_code',
                    $countryCode
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Type
            |--------------------------------------------------------------------------
            */

            if ($request->filled('type')) {

                $type = strtolower(
                    $request->type
                );

                if (!in_array(
                    $type,
                    ['airtime', 'data'],
                    true
                )) {
                    return response()->json([
                        'success' => false,
                        'message' =>
                            'Invalid voucher type.',
                    ], 422);
                }

                $query->where(
                    'type',
                    $type
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Network
            |--------------------------------------------------------------------------
            */

            if ($request->filled('network_id')) {

                $query->where(
                    'network_id',
                    $request->network_id
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Group products
            |--------------------------------------------------------------------------
            */

            $products = $query
                ->select([
                    'type',
                    'country_code',
                    'network_id',
                    'network_name',
                    'product_name',
                    'amount',
                    'currency',
                ])
                ->orderBy(
                    'country_code'
                )
                ->orderBy(
                    'network_name'
                )
                ->orderBy(
                    'amount'
                )
                ->get();

            return response()->json([
                'success' => true,

                'message' =>
                    'Voucher products loaded successfully.',

                'data' => $products,
            ]);

        } catch (Throwable $e) {

            return response()->json([
                'success' => false,

                'message' =>
                    $e->getMessage(),
            ], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK AVAILABILITY
    |--------------------------------------------------------------------------
    */

    public function availability(Request $request)
    {
        $request->validate([

            'type' =>
                ['required', 'in:airtime,data'],

            'country_code' =>
                ['required', 'string', 'size:2'],

            'amount' =>
                ['required', 'numeric', 'min:0.01'],

            'network_id' =>
                ['nullable', 'integer'],

            'product_name' =>
                ['nullable', 'string'],
        ]);

        try {

            $countryCode = strtoupper(
                $request->country_code
            );

            /*
            |--------------------------------------------------------------------------
            | Niger / Chad only
            |--------------------------------------------------------------------------
            */

            if (!in_array(
                $countryCode,
                ['NE', 'TD'],
                true
            )) {
                return response()->json([
                    'success' => false,
                    'message' =>
                        'Voucher service is available only for Niger and Chad.',
                ], 422);
            }

            $query = Voucher::query()
                ->where(
                    'status',
                    'available'
                )
                ->where(
                    'type',
                    strtolower(
                        $request->type
                    )
                )
                ->where(
                    'country_code',
                    $countryCode
                )
                ->where(
                    'amount',
                    $request->amount
                );

            if (
                $request->filled(
                    'network_id'
                )
            ) {
                $query->where(
                    'network_id',
                    $request->network_id
                );
            }

            if (
                $request->filled(
                    'product_name'
                )
            ) {
                $query->where(
                    'product_name',
                    $request->product_name
                );
            }

            $query->where(function ($q) {

                $q->whereNull(
                    'expires_at'
                )
                ->orWhere(
                    'expires_at',
                    '>',
                    now()
                );

            });

            $count = $query->count();

            return response()->json([

                'success' => true,

                'data' => [

                    'available' =>
                        $count > 0,

                    'quantity' =>
                        $count,

                ],

            ]);

        } catch (Throwable $e) {

            return response()->json([

                'success' => false,

                'message' =>
                    $e->getMessage(),

            ], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | PURCHASE
    |--------------------------------------------------------------------------
    */

    public function purchase(
        Request $request
    ) {
        $request->validate([

            'type' =>
                ['required', 'in:airtime,data'],

            'country_code' =>
                ['required', 'string', 'size:2'],

            'amount' =>
                ['required', 'numeric', 'min:0.01'],

            'pin' =>
                ['required', 'string'],

            'network_id' =>
                ['nullable', 'integer'],

            'product_name' =>
                ['nullable', 'string'],

        ]);

        try {

            $countryCode = strtoupper(
                $request->country_code
            );

            /*
            |--------------------------------------------------------------------------
            | Niger / Chad only
            |--------------------------------------------------------------------------
            */

            if (!in_array(
                $countryCode,
                ['NE', 'TD'],
                true
            )) {
                return response()->json([

                    'success' => false,

                    'message' =>
                        'Voucher service is available only for Niger and Chad.',

                ], 422);
            }

            /*
            |--------------------------------------------------------------------------
            | Purchase Voucher
            |--------------------------------------------------------------------------
            */

            $result =
                $this->purchaseService->purchase(

                    $request->user(),

                    [

                        'type' =>
                            strtolower(
                                $request->type
                            ),

                        'country_code' =>
                            $countryCode,

                        'amount' =>
                            (float)
                            $request->amount,

                        'pin' =>
                            $request->pin,

                        'network_id' =>
                            $request->network_id,

                        'product_name' =>
                            $request->product_name,

                    ]

                );

            return response()->json([

                'success' => true,

                'message' =>
                    'Voucher purchased successfully.',

                'data' =>
                    $result,

            ]);

        } catch (Throwable $e) {

            return response()->json([

                'success' => false,

                'message' =>
                    $e->getMessage(),

            ], 422);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | PURCHASE HISTORY
    |--------------------------------------------------------------------------
    */

    public function history(
        Request $request
    ) {
        try {

            $history = Voucher::query()

                ->where(
                    'user_id',
                    $request->user()->id
                )

                ->where(
                    'status',
                    'sold'
                )

                ->latest(
                    'sold_at'
                )

                ->paginate(20);

            /*
            |--------------------------------------------------------------------------
            | Do NOT expose all voucher inventory PINs here.
            |--------------------------------------------------------------------------
            */

            $history
                ->getCollection()
                ->transform(
                    function ($voucher) {

                        return [

                            'id' =>
                                $voucher->id,

                            'reference' =>
                                $voucher->purchase_reference,

                            'voucher_reference' =>
                                $voucher->reference,

                            'type' =>
                                $voucher->type,

                            'country_code' =>
                                $voucher->country_code,

                            'network_name' =>
                                $voucher->network_name,

                            'product_name' =>
                                $voucher->product_name,

                            'amount' =>
                                (float)
                                $voucher->amount,

                            'currency' =>
                                $voucher->currency,

                            'status' =>
                                'completed',

                            'sold_at' =>
                                $voucher->sold_at,

                            'expires_at' =>
                                $voucher->expires_at,

                        ];
                    }
                );

            return response()->json([

                'success' => true,

                'message' =>
                    'Voucher history loaded successfully.',

                'data' =>
                    $history,

            ]);

        } catch (Throwable $e) {

            return response()->json([

                'success' => false,

                'message' =>
                    $e->getMessage(),

            ], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | RECEIPT
    |--------------------------------------------------------------------------
    |
    | The receipt returns the PIN because the customer has already
    | purchased the voucher.
    |
    */

    public function receipt(
        Request $request,
        string $reference
    ) {
        try {

            $voucher = Voucher::query()

                ->where(
                    'user_id',
                    $request->user()->id
                )

                ->where(
                    'purchase_reference',
                    $reference
                )

                ->where(
                    'status',
                    'sold'
                )

                ->first();

            if (!$voucher) {

                return response()->json([

                    'success' => false,

                    'message' =>
                        'Voucher receipt not found.',

                ], 404);
            }

            return response()->json([

                'success' => true,

                'message' =>
                    'Voucher receipt loaded successfully.',

                'data' => [

                    'reference' =>
                        $voucher->purchase_reference,

                    'voucher_reference' =>
                        $voucher->reference,

                    'type' =>
                        $voucher->type,

                    'country_code' =>
                        $voucher->country_code,

                    'network' => [

                        'id' =>
                            $voucher->network_id,

                        'name' =>
                            $voucher->network_name,

                    ],

                    'product_name' =>
                        $voucher->product_name,

                    'amount' =>
                        (float)
                        $voucher->amount,

                    'currency' =>
                        $voucher->currency,

                    'fee' =>
                        0,

                    'total' =>
                        (float)
                        $voucher->amount,

                    /*
                    |--------------------------------------------------------------------------
                    | ACTUAL CARD / VOUCHER PIN
                    |--------------------------------------------------------------------------
                    */

                    'pin' =>
                        $voucher->pin,

                    'status' =>
                        'completed',

                    'provider' =>
                        $voucher->provider,

                    'provider_reference' =>
                        $voucher->provider_reference,

                    'date' =>
                        optional(
                            $voucher->sold_at
                        )->format(
                            'd M Y H:i'
                        ),

                    'expires_at' =>
                        optional(
                            $voucher->expires_at
                        )->format(
                            'd M Y H:i'
                        ),

                ],

            ]);

        } catch (Throwable $e) {

            return response()->json([

                'success' => false,

                'message' =>
                    $e->getMessage(),

            ], 500);
        }
    }
}