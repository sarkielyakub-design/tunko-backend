<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WalletDepositController extends Controller
{
    /**
     * Create Deposit Request
     */
    public function requestDeposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:5000',
        ]);

        $user = $request->user();

        $wallet = Wallet::where(
            'user_id',
            $user->id
        )->firstOrFail();

        $deposit = Deposit::create([

            'user_id' => $user->id,

            'wallet_id' => $wallet->id,

            'reference' => 'DEP-' . strtoupper(Str::random(12)),

            'amount' => $request->amount,

            'fee' => 0,

            'total' => $request->amount,

            'currency' => $wallet->currency,

            'status' => 'pending',

            'gateway' => 'manual',

            'payment_method' => 'bank_transfer',

        ]);

        return response()->json([

            'success' => true,

            'message' => 'Deposit request submitted successfully.',

            'deposit' => $deposit,

            'bank_details' => [

                'bank_name' => 'Your Bank Name',

                'account_name' => 'Tunko Money',

                'account_number' => '1234567890',

            ],

        ]);
    }

    /**
     * Deposit History
     */
    public function history(Request $request)
    {
        return response()->json([

            'success' => true,

            'data' => Deposit::where(
                'user_id',
                $request->user()->id
            )
            ->latest()
            ->paginate(20),

        ]);
    }

    /**
     * Deposit Details
     */
    public function show(Request $request, Deposit $deposit)
    {
        if ($deposit->user_id !== $request->user()->id) {

            return response()->json([
                'success' => false,
                'message' => 'Deposit not found.'
            ], 404);

        }

        return response()->json([

            'success' => true,

            'data' => $deposit,

        ]);
    }
}