<?php

namespace App\Http\Controllers\Api\V1;
use App\Http\Requests\DepositRequest;
use App\Models\Deposit;
use App\Models\Transaction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Wallet;

class WalletController extends Controller
{
    /**
     * Get full wallet information
     */
    public function show(Request $request)
{
    $user = $request->user();

    $wallet = $user->wallet;

    if (!$wallet) {
        return response()->json([
            'success' => false,
            'message' => 'Wallet not found.',
        ], 404);
    }

    return response()->json([
        'success' => true,

        'wallet' => [
            'wallet_number' => $wallet->wallet_number,
            'balance' => (float) $wallet->balance,
            'currency' => $wallet->currency,
            'is_active' => (bool) $wallet->is_active,
            'created_at' => $wallet->created_at,
        ],

        'user' => [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'is_verified' => (bool) $user->is_verified,
        ],
    ]);
}

    /**
     * Get wallet balance only
     */
    public function balance(Request $request)
    {
        $wallet = $request->user()->wallet;

        if (!$wallet) {
            return response()->json([
                'success' => false,
                'message' => 'Wallet not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'balance' => $wallet->balance,
            'currency' => $wallet->currency,
        ]);
    }

    /**
     * Wallet summary for dashboard
     */
    public function summary(Request $request)
    {
        $wallet = $request->user()->wallet;

        if (!$wallet) {
            return response()->json([
                'success' => false,
                'message' => 'Wallet not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'wallet_number' => $wallet->wallet_number,
                'balance' => $wallet->balance,
                'currency' => $wallet->currency,
                'user' => [
                    'id' => $request->user()->id,
                    'name' => $request->user()->first_name . ' ' . $request->user()->last_name,
                    'email' => $request->user()->email,
                    'phone' => $request->user()->phone,
                ],
            ],
        ]);
    }
    public function deposit(
    DepositRequest $request
)
{
    $wallet = $request->user()->wallet;

    $wallet->increment(
        'balance',
        $request->amount
    );

    Deposit::create([
        'user_id' => $request->user()->id,
        'amount' => $request->amount,
        'reference' => 'DEP'.time(),
        'status' => 'success',
    ]);

    Transaction::create([
        'user_id' => $request->user()->id,
        'type' => 'credit',
        'amount' => $request->amount,
        'reference' => 'DEP'.time(),
        'status' => 'success',
        'description' => 'Wallet Funding',
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Wallet funded successfully.',
        'balance' => $wallet->fresh()->balance,
    ]);
}
}