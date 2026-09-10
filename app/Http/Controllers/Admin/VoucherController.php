<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VoucherStoreRequest;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class VoucherController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LIST VOUCHERS
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query = Voucher::query()
            ->with('network')
            ->latest();

        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */

        if ($request->filled('type')) {
            $query->where(
                'type',
                $request->type
            );
        }

        if ($request->filled('country_code')) {
            $query->where(
                'country_code',
                strtoupper(
                    $request->country_code
                )
            );
        }

        if ($request->filled('network_id')) {
            $query->where(
                'network_id',
                $request->network_id
            );
        }

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->status
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search =
                $request->search;

            $query->where(function ($q) use ($search) {

                $q->where(
                    'reference',
                    'like',
                    "%{$search}%"
                )

                ->orWhere(
                    'network_name',
                    'like',
                    "%{$search}%"
                )

                ->orWhere(
                    'product_name',
                    'like',
                    "%{$search}%"
                );
            });
        }

        $vouchers =
            $query->paginate(
                $request->integer(
                    'per_page',
                    20
                )
            );

        /*
        |--------------------------------------------------------------------------
        | Never expose PINs in inventory listing
        |--------------------------------------------------------------------------
        */

        $vouchers->getCollection()
            ->transform(function ($voucher) {

                return [
                    'id' =>
                        $voucher->id,

                    'reference' =>
                        $voucher->reference,

                    'type' =>
                        $voucher->type,

                    'country_code' =>
                        $voucher->country_code,

                    'network_id' =>
                        $voucher->network_id,

                    'network_name' =>
                        $voucher->network_name,

                    'product_name' =>
                        $voucher->product_name,

                    'amount' =>
                        (float) $voucher->amount,

                    'currency' =>
                        $voucher->currency,

                    'status' =>
                        $voucher->status,

                    'provider' =>
                        $voucher->provider,

                    'provider_reference' =>
                        $voucher->provider_reference,

                    'user_id' =>
                        $voucher->user_id,

                    'purchase_reference' =>
                        $voucher->purchase_reference,

                    'sold_at' =>
                        $voucher->sold_at,

                    'expires_at' =>
                        $voucher->expires_at,

                    'remark' =>
                        $voucher->remark,

                    'created_at' =>
                        $voucher->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $vouchers,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE VOUCHER
    |--------------------------------------------------------------------------
    */

    public function store(
        VoucherStoreRequest $request
    ) {
        try {

            $voucher =
                Voucher::create([

                    'reference' =>
                        $request->reference
                        ?: $this->generateReference(),

                    'type' =>
                        $request->type,

                    'country_code' =>
                        strtoupper(
                            $request->country_code
                        ),

                    'network_id' =>
                        $request->network_id,

                    'network_name' =>
                        $request->network_name,

                    'product_name' =>
                        $request->product_name,

                    'amount' =>
                        $request->amount,

                    'currency' =>
                        strtoupper(
                            $request->currency
                        ),

                    'pin' =>
                        $request->pin,

                    'status' =>
                        'available',

                    'provider' =>
                        $request->provider,

                    'provider_reference' =>
                        $request->provider_reference,

                    'expires_at' =>
                        $request->expires_at,

                    'remark' =>
                        $request->remark,

                    'meta' =>
                        $request->meta,
                ]);

            return response()->json([

                'success' =>
                    true,

                'message' =>
                    'Voucher added successfully.',

                'data' => [

                    'id' =>
                        $voucher->id,

                    'reference' =>
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
                        (float) $voucher->amount,

                    'currency' =>
                        $voucher->currency,

                    'status' =>
                        $voucher->status,

                ],

            ], 201);

        } catch (Throwable $e) {

            Log::error(
                'Admin voucher creation failed',
                [
                    'error' =>
                        $e->getMessage(),
                ]
            );

            return response()->json([

                'success' =>
                    false,

                'message' =>
                    'Unable to add voucher.',

            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW VOUCHER
    |--------------------------------------------------------------------------
    */

    public function show(
        Voucher $voucher
    ) {
        return response()->json([

            'success' =>
                true,

            'data' => [

                'id' =>
                    $voucher->id,

                'reference' =>
                    $voucher->reference,

                'type' =>
                    $voucher->type,

                'country_code' =>
                    $voucher->country_code,

                'network_id' =>
                    $voucher->network_id,

                'network_name' =>
                    $voucher->network_name,

                'product_name' =>
                    $voucher->product_name,

                'amount' =>
                    (float) $voucher->amount,

                'currency' =>
                    $voucher->currency,

                /*
                | PIN intentionally omitted.
                */

                'status' =>
                    $voucher->status,

                'provider' =>
                    $voucher->provider,

                'provider_reference' =>
                    $voucher->provider_reference,

                'user_id' =>
                    $voucher->user_id,

                'purchase_reference' =>
                    $voucher->purchase_reference,

                'sold_at' =>
                    $voucher->sold_at,

                'expires_at' =>
                    $voucher->expires_at,

                'remark' =>
                    $voucher->remark,

                'created_at' =>
                    $voucher->created_at,

            ],

        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE AVAILABLE VOUCHER
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Voucher $voucher
    ) {
        if (
            $voucher->status !==
            'available'
        ) {
            return response()->json([

                'success' =>
                    false,

                'message' =>
                    'Only available vouchers can be deleted.',

            ], 422);
        }

        $voucher->delete();

        return response()->json([

            'success' =>
                true,

            'message' =>
                'Voucher deleted successfully.',

        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CANCEL VOUCHER
    |--------------------------------------------------------------------------
    */

    public function cancel(
        Voucher $voucher
    ) {
        if (
            $voucher->status !==
            'available'
        ) {
            return response()->json([

                'success' =>
                    false,

                'message' =>
                    'Only available vouchers can be cancelled.',

            ], 422);
        }

        $voucher->update([
            'status' =>
                'cancelled',
        ]);

        return response()->json([

            'success' =>
                true,

            'message' =>
                'Voucher cancelled successfully.',

        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | GENERATE REFERENCE
    |--------------------------------------------------------------------------
    */

    private function generateReference(): string
    {
        do {

            $reference =
                'VCH-' .
                strtoupper(
                    Str::random(12)
                );

        } while (
            Voucher::where(
                'reference',
                $reference
            )->exists()
        );

        return $reference;
    }
}
