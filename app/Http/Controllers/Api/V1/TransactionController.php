<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Get authenticated user's recent transactions.
     */
    public function index(Request $request)
    {
        $transactions = $request->user()
            ->transactions()
            ->latest()
            ->take(10)
            ->get();

        return response()->json([
            'success' => true,
            'transactions' => $transactions,
        ]);
    }

    /**
     * Get transaction receipt data.
     */
    public function receipt(string $reference)
    {
        $transaction = Transaction::with([
            'user.wallet',
        ])
            ->where('reference', $reference)
            ->firstOrFail();

        return response()->json([
            'success' => true,

            'data' => [
                'reference' => $transaction->reference,

                'amount' => $transaction->amount,

                'status' => $transaction->status,

                'type' => $transaction->type,

                'payment_method' =>
                    $transaction->payment_method,

                'date' => $transaction->created_at,

                'description' =>
                    $transaction->description,

                'currency' => 'NGN',

                'wallet_number' =>
                    $transaction->user?->wallet?->wallet_number,

                'user' => [
                    'name' =>
                        $transaction->user?->full_name,

                    'email' =>
                        $transaction->user?->email,
                ],
            ],
        ]);
    }
}