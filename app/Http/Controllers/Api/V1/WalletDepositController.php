<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WalletDepositController extends Controller
{
    protected PaystackService $paystack;

    public function __construct(
        PaystackService $paystack
    ) {
        $this->paystack = $paystack;
    }

    /**
     * Initialize Deposit
     */
    public function initialize(Request $request)
    {
        $request->validate([
            'amount' => [
                'required',
                'numeric',
                'min:100'
            ],
        ]);

        $user = $request->user();

        $reference = 'TNK-' . strtoupper(
            Str::random(12)
        );

        $transaction = WalletTransaction::create([

            'user_id' => $user->id,

            'reference' => $reference,

            'type' => 'deposit',

            'amount' => $request->amount,

            'fee' => 0,

            'total' => $request->amount,

            'currency' => 'NGN',

            'status' => 'pending',

            'payment_gateway' => 'paystack',

            'description' => 'Wallet Deposit',

        ]);

        $response = $this->paystack->initialize(

            $user->email,

            $request->amount,

            $reference,

            [
                'user_id' => $user->id,
            ]

        );

        if (!($response['status'] ?? false)) {

            return response()->json([
                'success' => false,
                'message' => 'Unable to initialize payment.',
            ], 422);

        }

        return response()->json([

            'success' => true,

            'message' => 'Payment initialized.',

            'reference' => $reference,

            'authorization_url'
                => $response['data']['authorization_url'],

            'access_code'
                => $response['data']['access_code'],

        ]);
    }

    /**
     * Verify Deposit
     */
    public function verify(Request $request)
    {
        $request->validate([
            'reference' => 'required'
        ]);

        $response = $this->paystack->verify(
            $request->reference
        );

        if (!($response['status'] ?? false)) {

            return response()->json([
                'success' => false,
                'message' => 'Verification failed.',
            ], 422);

        }

        $payment = $response['data'];

        if ($payment['status'] != 'success') {

            return response()->json([
                'success' => false,
                'message' => 'Payment not successful.',
            ], 422);

        }

        DB::beginTransaction();

        try {

            $transaction = WalletTransaction::where(
                'reference',
                $request->reference,
            )->firstOrFail();

            if ($transaction->status == 'success') {

                DB::rollBack();

                return response()->json([
                    'success' => true,
                    'message' => 'Already verified.',
                ]);

            }

            $wallet = Wallet::where(
                'user_id',
                $transaction->user_id,
            )->firstOrFail();

            $wallet->increment(
                'balance',
                $transaction->amount,
            );

            $transaction->update([

                'status' => 'success',

                'gateway_reference'
                    => $payment['reference'],

                'completed_at' => now(),

                'meta' => $payment,

            ]);

            DB::commit();

            return response()->json([

                'success' => true,

                'message'
                    => 'Wallet funded successfully.',

                'balance'
                    => $wallet->fresh()->balance,

            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([

                'success' => false,

                'message' => $e->getMessage(),

            ], 500);

        }
    }
}