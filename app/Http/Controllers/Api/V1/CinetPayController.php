<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\CinetPay\Services\CinetPayPaymentService;
use Illuminate\Http\Request;
use Throwable;

class CinetPayController extends Controller
{
    public function __construct(
        protected CinetPayPaymentService $paymentService
    ) {}

    /**
     * Initialize Payment
     */
    public function initialize(Request $request)
    {
        $request->validate([
            'amount' => [
                'required',
                'numeric',
                'min:100',
            ],
        ]);

        try {

            $payment = $this->paymentService->initialize(
                $request->user(),
                $request->amount
            );

            return response()->json([
                'success' => true,
                'message' => 'Payment initialized successfully.',
                'data' => $payment,
            ]);

        } catch (Throwable $e) {

            report($e);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);

        }
    }

    /**
     * Verify Payment
     */
    public function verify(Request $request)
    {
        $request->validate([
            'reference' => 'required|string',
        ]);

        try {

            $verification = $this->paymentService->verify(
                $request->reference
            );

            return response()->json([
                'success' => true,
                'data' => $verification,
            ]);

        } catch (Throwable $e) {

            report($e);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);

        }
    }

    /**
     * Webhook
     */
    public function webhook(Request $request)
    {
        try {

            $reference = $request->input('transaction_id');

            $verification = $this->paymentService->verify(
                $reference
            );

            if (
                ($verification['code'] ?? null) === '00'
            ) {
                $this->paymentService->creditWallet(
                    $reference
                );
            }

            return response()->json([
                'success' => true,
            ]);

        } catch (Throwable $e) {

            report($e);

            return response()->json([
                'success' => false,
            ], 500);

        }
    }
}