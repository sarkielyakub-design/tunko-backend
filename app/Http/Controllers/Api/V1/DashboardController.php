<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $wallet = $user->wallet;

        $transactions = Transaction::where(
            'user_id',
            $user->id
        );

        $recentTransactions = $transactions
            ->clone()
            ->latest()
            ->take(5)
            ->get();

        $totalSent = Transaction::where(
            'user_id',
            $user->id
        )
        ->whereIn('type', [
            'wallet_transfer',
            'airtime',
            'data',
            'debit',
        ])
        ->where('status', 'success')
        ->sum('amount');

        $totalReceived = Transaction::where(
            'user_id',
            $user->id
        )
        ->whereIn('type', [
            'wallet_received',
            'credit',
        ])
        ->where('status', 'success')
        ->sum('amount');

        $transactionCount = Transaction::where(
            'user_id',
            $user->id
        )->count();

        $beneficiaryCount = $user->beneficiaries()->count();

        return response()->json([
            "success" => true,

            "user" => [
                "id" => $user->id,
                "first_name" => $user->first_name,
                "last_name" => $user->last_name,
                "full_name" => $user->full_name,
                "email" => $user->email,
                "phone" => $user->phone,
                "is_verified" => (bool) $user->is_verified,
            ],

            "wallet" => [
                "wallet_number" => $wallet?->wallet_number,
                "balance" => $wallet?->balance ?? 0,
                "currency" => $wallet?->currency ?? "NGN",
                "is_active" => (bool) ($wallet?->is_active),
            ],

            "stats" => [
                "total_sent" => $totalSent,
                "total_received" => $totalReceived,
                "transactions" => $transactionCount,
                "beneficiaries" => $beneficiaryCount,
            ],

            "recent_transactions" => $recentTransactions,

            "exchange_rates" => [
                [
                    "from" => "USD",
                    "to" => "NGN",
                    "rate" => 1585,
                ],
                [
                    "from" => "EUR",
                    "to" => "NGN",
                    "rate" => 1712,
                ],
                [
                    "from" => "GBP",
                    "to" => "NGN",
                    "rate" => 2054,
                ],
                [
                    "from" => "XOF",
                    "to" => "NGN",
                    "rate" => 2.65,
                ],
            ],

            "notifications" => 0,

            "kyc_status" => optional($user->kyc)->status
                ?? "not_submitted",
        ]);
    }
}