<?php

namespace App\Services\Admin;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WalletService
{
    /**
     * List Wallets
     */
    public function index(array $filters)
    {
        return Wallet::query()

            ->with('user')

            ->withCount('walletTransactions')

            ->when(
                $filters['search'] ?? null,
                function ($query, $search) {

                    $query->where(function ($q) use ($search) {

                        $q->where('wallet_number', 'like', "%{$search}%")

                            ->orWhereHas('user', function ($user) use ($search) {

                                $user->where('first_name', 'like', "%{$search}%")
                                    ->orWhere('last_name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%")
                                    ->orWhere('phone', 'like', "%{$search}%");

                            });

                    });

                }
            )

            ->when(
                $filters['currency'] ?? null,
                fn ($q, $currency) =>
                    $q->where('currency', $currency)
            )

            ->when(
                isset($filters['is_active']),
                fn ($q) =>
                    $q->where('is_active', $filters['is_active'])
            )

            ->when(
                $filters['min_balance'] ?? null,
                fn ($q, $amount) =>
                    $q->where('balance', '>=', $amount)
            )

            ->when(
                $filters['max_balance'] ?? null,
                fn ($q, $amount) =>
                    $q->where('balance', '<=', $amount)
            )

            ->orderBy(
                $filters['sort'] ?? 'created_at',
                $filters['direction'] ?? 'desc'
            )

            ->paginate(
                $filters['per_page'] ?? 20
            );
    }

    /**
     * Wallet Details
     */
    public function show(Wallet $wallet): Wallet
    {
        return $wallet
            ->load('user')
            ->loadCount('walletTransactions');
    }

    /**
     * Credit Wallet
     */
    public function credit(
        Wallet $wallet,
        array $data
    ): Wallet {

        return DB::transaction(function () use ($wallet, $data) {

            $oldBalance = $wallet->balance;

            $wallet->increment(
                'balance',
                $data['amount']
            );

            WalletTransaction::create([

                'user_id' => $wallet->user_id,

                'reference' => $data['reference']
                    ?? strtoupper(Str::random(16)),

                'type' => 'deposit',

                'amount' => $data['amount'],

                'fee' => 0,

                'total' => $data['amount'],

                'currency' => $wallet->currency,

                'status' => 'success',

                'description' => $data['reason'],

                'meta' => [

                    'old_balance' => $oldBalance,

                    'new_balance' => $wallet->fresh()->balance,

                    'admin_id' => Auth::guard('admin')->id(),

                    'note' => $data['note'] ?? null,

                ],

                'completed_at' => now(),

            ]);

            return $wallet->fresh()
                ->load('user')
                ->loadCount('walletTransactions');

        });

    }

    /**
     * Debit Wallet
     */
    public function debit(
        Wallet $wallet,
        array $data
    ): Wallet {

        return DB::transaction(function () use ($wallet, $data) {

            if (
                empty($data['allow_negative_balance']) &&
                $wallet->balance < $data['amount']
            ) {

                throw new \Exception(
                    'Insufficient wallet balance.'
                );

            }

            $oldBalance = $wallet->balance;

            $wallet->decrement(
                'balance',
                $data['amount']
            );

            WalletTransaction::create([

                'user_id' => $wallet->user_id,

                'reference' => $data['reference']
                    ?? strtoupper(Str::random(16)),

                'type' => 'withdraw',

                'amount' => $data['amount'],

                'fee' => 0,

                'total' => $data['amount'],

                'currency' => $wallet->currency,

                'status' => 'success',

                'description' => $data['reason'],

                'meta' => [

                    'old_balance' => $oldBalance,

                    'new_balance' => $wallet->fresh()->balance,

                    'admin_id' => Auth::guard('admin')->id(),

                    'note' => $data['note'] ?? null,

                ],

                'completed_at' => now(),

            ]);

            return $wallet->fresh()
                ->load('user')
                ->loadCount('walletTransactions');

        });

    }

    /**
     * Freeze Wallet
     */
    public function freeze(
        Wallet $wallet,
        array $data
    ): Wallet {

        $wallet->update([

            'is_active' => false,

        ]);

        return $wallet->fresh()
            ->load('user');
    }

    /**
     * Unfreeze Wallet
     */
    public function unfreeze(
        Wallet $wallet
    ): Wallet {

        $wallet->update([

            'is_active' => true,

        ]);

        return $wallet->fresh()
            ->load('user');
    }

    /**
     * Wallet Statement
     */
    public function statement(
        Wallet $wallet
    )
    {
        return WalletTransaction::where(
                'user_id',
                $wallet->user_id
            )
            ->latest()
            ->paginate(50);
    }

    /**
     * Wallet Transactions
     */
    public function transactions(
        Wallet $wallet
    )
    {
        return WalletTransaction::where(
                'user_id',
                $wallet->user_id
            )
            ->latest()
            ->paginate(20);
    }

    /**
     * Wallet Summary
     */
    public function summary(): array
    {
        return [

            'total_wallets' => Wallet::count(),

            'active_wallets' => Wallet::where(
                'is_active',
                true
            )->count(),

            'inactive_wallets' => Wallet::where(
                'is_active',
                false
            )->count(),

            'total_balance' => Wallet::sum(
                'balance'
            ),

        ];
    }
}