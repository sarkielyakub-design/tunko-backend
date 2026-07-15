<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FundingController extends Controller
{
    public function fund(Request $request)
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:100']
        ]);

        $wallet = $request->user()->wallet;

        $wallet->increment('balance', $request->amount);

        Transaction::create([
            'user_id' => $request->user()->id,
            'reference' => 'DEP-' . strtoupper(Str::random(12)),
            'type' => 'deposit',
            'amount' => $request->amount,
            'fee' => 0,
            'total' => $request->amount,
            'status' => 'completed',
            'description' => 'Wallet Funding',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Wallet funded successfully',
            'balance' => $wallet->fresh()->balance,
        ]);
    }
}