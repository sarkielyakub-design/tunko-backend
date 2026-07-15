<?php

namespace App\Services\Admin;

use App\Models\Withdrawal;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WithdrawalService
{
    /**
     * Withdrawal List
     */
    public function index(array $filters)
    {
        return Withdrawal::query()

            ->with([
                'user',
                'wallet',
            ])

            ->when(
                $filters['search'] ?? null,
                function ($query, $search) {

                    $query->where(function ($q) use ($search) {

                        $q->where('reference', 'like', "%{$search}%")

                            ->orWhere(
                                'provider_reference',
                                'like',
                                "%{$search}%"
                            )

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
                $filters['status'] ?? null,
                fn($q, $status) => $q->where('status', $status)
            )

            ->when(
                $filters['provider'] ?? null,
                fn($q, $provider) => $q->where('provider', $provider)
            )

            ->when(
                $filters['currency'] ?? null,
                fn($q, $currency) => $q->where('currency', $currency)
            )

            ->when(
                $filters['user_id'] ?? null,
                fn($q, $user) => $q->where('user_id', $user)
            )

            ->latest()

            ->paginate(
                $filters['per_page'] ?? 20
            );
    }

    /**
     * Withdrawal Details
     */
    public function show(
        Withdrawal $withdrawal
    ): Withdrawal {

        return $withdrawal->load([
            'user',
            'wallet',
        ]);

    }

    /**
     * Approve Withdrawal
     */
    public function approve(
        Withdrawal $withdrawal,
        array $data
    ): Withdrawal {

        return DB::transaction(function () use (
            $withdrawal,
            $data
        ) {

            if ($withdrawal->status !== 'pending') {

                throw new \Exception(
                    'Only pending withdrawals can be approved.'
                );

            }

            if ($data['debit_wallet']) {

                $wallet = Wallet::where(
                    'id',
                    $withdrawal->wallet_id
                )->lockForUpdate()->first();

                if (!$wallet) {

                    throw new \Exception(
                        'Wallet not found.'
                    );

                }

                if ($wallet->balance < $withdrawal->total) {

                    throw new \Exception(
                        'Insufficient wallet balance.'
                    );

                }

                $wallet->decrement(
                    'balance',
                    $withdrawal->total
                );

            }

            Transaction::create([

                'user_id' => $withdrawal->user_id,

                'reference' => $withdrawal->reference,

                'type' => 'withdrawal',

                'amount' => $withdrawal->amount,

                'fee' => $withdrawal->fee,

                'total' => $withdrawal->total,

                'currency' => $withdrawal->currency,

                'status' => 'processing',

                'payment_gateway' => $data['provider'],

                'description' => 'Wallet Withdrawal',

            ]);

            $withdrawal->update([

                'status' => 'processing',

                'provider' => $data['provider'],

                'provider_reference' => $data['provider_reference'] ?? null,

                'provider_status' => $data['provider_status'],

                'provider_response' => $data['provider_response'] ?? null,

                'approved_by' => Auth::guard('admin')->id(),

                'approved_at' => now(),

                'admin_note' => $data['note'] ?? null,

            ]);

            return $withdrawal->fresh()->load([
                'user',
                'wallet',
            ]);

        });

    }

    /**
     * Reject Withdrawal
     */
    public function reject(
        Withdrawal $withdrawal,
        array $data
    ): Withdrawal {

        return DB::transaction(function () use (
            $withdrawal,
            $data
        ) {

            $withdrawal->update([

                'status' => 'rejected',

                'reject_reason' => $data['reason'],

                'reject_code' => $data['reject_code'],

                'admin_note' => $data['note'] ?? null,

            ]);

            if ($data['refund_wallet']) {

                $wallet = Wallet::lockForUpdate()
                    ->find($withdrawal->wallet_id);

                if ($wallet) {

                    $wallet->increment(
                        'balance',
                        $withdrawal->total
                    );

                }

            }

            return $withdrawal->fresh()->load([
                'user',
                'wallet',
            ]);

        });

    }

    /**
     * Cancel Withdrawal
     */
    public function cancel(
        Withdrawal $withdrawal,
        array $data
    ): Withdrawal {

        return DB::transaction(function () use (
            $withdrawal,
            $data
        ) {

            $withdrawal->update([

                'status' => 'cancelled',

                'cancel_reason' => $data['reason'],

                'cancel_code' => $data['cancel_code'],

                'cancelled_at' => now(),

                'admin_note' => $data['note'] ?? null,

            ]);

            if ($data['refund_wallet']) {

                $wallet = Wallet::lockForUpdate()
                    ->find($withdrawal->wallet_id);

                if ($wallet) {

                    $wallet->increment(
                        'balance',
                        $withdrawal->total
                    );

                }

            }

            return $withdrawal->fresh()->load([
                'user',
                'wallet',
            ]);

        });

    }

    /**
     * Retry Withdrawal
     */
    public function retry(
        Withdrawal $withdrawal,
        array $data
    ): Withdrawal {

        return DB::transaction(function () use (
            $withdrawal,
            $data
        ) {

            $withdrawal->increment(
                'retry_count'
            );

            $withdrawal->update([

                'status' => 'processing',

                'provider' => $data['provider']
                    ?? $withdrawal->provider,

                'last_retry_at' => now(),

                'admin_note' => $data['note'] ?? null,

            ]);

            return $withdrawal->fresh()->load([
                'user',
                'wallet',
            ]);

        });

    }

    /**
     * Statistics
     */
    public function statistics(): array
    {
        return [

            'total_withdrawals' => Withdrawal::count(),

            'pending' => Withdrawal::where(
                'status',
                'pending'
            )->count(),

            'processing' => Withdrawal::where(
                'status',
                'processing'
            )->count(),

            'completed' => Withdrawal::where(
                'status',
                'completed'
            )->count(),

            'failed' => Withdrawal::where(
                'status',
                'failed'
            )->count(),

            'cancelled' => Withdrawal::where(
                'status',
                'cancelled'
            )->count(),

            'total_volume' => Withdrawal::sum(
                'amount'
            ),

            'today_volume' => Withdrawal::whereDate(
                'created_at',
                today()
            )->sum('amount'),

        ];
    }
}