<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
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
public function receipt($reference)
{
    $transaction = Transaction::where(
        'reference',
        $reference
    )->firstOrFail();

    return response()->json([
        'success' => true,

        'data' => [

            'reference' => $transaction->reference,

            'amount' => $transaction->amount,

            'status' => $transaction->status,

            'type' => $transaction->type,

            'payment_method' => $transaction->payment_method,

            'date' => $transaction->created_at,

            'description' => $transaction->description,

            'currency' => 'NGN',

            'wallet_number' => $transaction
                ->user
                ->wallet
                ->wallet_number,

            'user' => [
                'name' => $transaction->user->full_name,
                'email' => $transaction->user->email,
            ],
        ],
    ]);
}
    
}