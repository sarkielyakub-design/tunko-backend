<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\OfficeTransferQuoteRequest;
use App\Http\Requests\OfficeTransferRequest;
use App\Services\OfficeTransferService;
use Illuminate\Http\Request;
use Throwable;

class OfficeTransferController extends Controller
{
    public function __construct(
        protected OfficeTransferService $officeTransferService
    ) {}


    /*
    |--------------------------------------------------------------------------
    | DESTINATIONS
    |--------------------------------------------------------------------------
    */

    public function destinations()
    {
        try {

            $destinations =
                $this->officeTransferService
                    ->destinations();

            return response()->json([

                'success' =>
                    true,

                'message' =>
                    'Office transfer destinations loaded successfully.',

                'data' =>
                    $destinations,
            ]);

        } catch (Throwable $e) {

            return response()->json([

                'success' =>
                    false,

                'message' =>
                    $e->getMessage(),

            ], 422);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | QUOTE
    |--------------------------------------------------------------------------
    */

    public function quote(
        OfficeTransferQuoteRequest $request
    ) {
        try {

            $quote =
                $this->officeTransferService->quote(
                    $request->user(),
                    $request->validated()
                );

            return response()->json([

                'success' =>
                    true,

                'message' =>
                    'Office transfer quote calculated successfully.',

                'data' =>
                    $quote,
            ]);

        } catch (Throwable $e) {

            return response()->json([

                'success' =>
                    false,

                'message' =>
                    $e->getMessage(),

            ], 422);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | SEND
    |--------------------------------------------------------------------------
    */

    public function send(
        OfficeTransferRequest $request
    ) {
        try {

            $transfer =
                $this->officeTransferService->send(
                    $request->user(),
                    $request->validated()
                );

            return response()->json([

                'success' =>
                    true,

                'message' =>
                    'Office transfer created successfully.',

                'data' =>
                    $transfer,
            ]);

        } catch (Throwable $e) {

            return response()->json([

                'success' =>
                    false,

                'message' =>
                    $e->getMessage(),

            ], 422);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | HISTORY
    |--------------------------------------------------------------------------
    */

    public function history(
        Request $request
    ) {
        try {

            $history =
                $this->officeTransferService->history(
                    $request->user()
                );

            return response()->json([

                'success' =>
                    true,

                'data' =>
                    $history,
            ]);

        } catch (Throwable $e) {

            return response()->json([

                'success' =>
                    false,

                'message' =>
                    $e->getMessage(),

            ], 422);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | RECEIPT
    |--------------------------------------------------------------------------
    */

    public function receipt(
        string $reference,
        Request $request
    ) {
        try {

            $receipt =
                $this->officeTransferService->receipt(
                    $reference,
                    $request->user()
                );

            return response()->json([

                'success' =>
                    true,

                'message' =>
                    'Office transfer receipt loaded successfully.',

                'data' =>
                    $receipt,
            ]);

        } catch (Throwable $e) {

            return response()->json([

                'success' =>
                    false,

                'message' =>
                    $e->getMessage(),

            ], 404);
        }
    }
}