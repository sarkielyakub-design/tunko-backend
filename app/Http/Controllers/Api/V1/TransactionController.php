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
     * Get authenticated user's transaction receipt.
     */
    public function receipt(Request $request, string $reference)
    {
        $transaction = $request->user()
            ->transactions()
            ->with('user.wallet')
            ->where('reference', $reference)
            ->first();

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction receipt not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Transaction receipt loaded successfully.',
            'data' => [
                'reference' => $transaction->reference,
                'amount' => $transaction->amount,
                'status' => $transaction->status,
                'type' => $transaction->type,
                'payment_method' => $transaction->payment_method,
                'date' => $transaction->created_at,
                'description' => $transaction->description,

                // Use the transaction currency if available.
                'currency' => $transaction->currency ?? 'NGN',

                'wallet_number' =>
                    $transaction->user?->wallet?->wallet_number,

                'user' => [
                    'name' => $transaction->user?->full_name,
                    'email' => $transaction->user?->email,
                ],
            ],
        ]);
    }
}