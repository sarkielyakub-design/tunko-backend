<?php

namespace App\Services\Admin;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TransactionService
{
    /**
     * Transaction List
     */
    public function index(array $filters)
    {
        return Transaction::query()

            ->with('user')

            ->when(
                $filters['search'] ?? null,
                function ($query, $search) {

                    $query->where(function ($q) use ($search) {

                        $q->where('reference', 'like', "%{$search}%")

                            ->orWhere('gateway_reference', 'like', "%{$search}%")

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
                $filters['type'] ?? null,
                fn($q, $type) => $q->where('type', $type)
            )

            ->when(
                $filters['payment_gateway'] ?? null,
                fn($q, $gateway) => $q->where('payment_gateway', $gateway)
            )

            ->when(
                $filters['currency'] ?? null,
                fn($q, $currency) => $q->where('currency', $currency)
            )

            ->when(
                $filters['user_id'] ?? null,
                fn($q, $user) => $q->where('user_id', $user)
            )

            ->when(
                $filters['min_amount'] ?? null,
                fn($q, $amount) => $q->where('amount', '>=', $amount)
            )

            ->when(
                $filters['max_amount'] ?? null,
                fn($q, $amount) => $q->where('amount', '<=', $amount)
            )

            ->when(
                $filters['from_date'] ?? null,
                fn($q, $date) => $q->whereDate('created_at', '>=', $date)
            )

            ->when(
                $filters['to_date'] ?? null,
                fn($q, $date) => $q->whereDate('created_at', '<=', $date)
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
     * Show Transaction
     */
    public function show(Transaction $transaction): Transaction
    {
        return $transaction->load('user');
    }

    /**
     * Refund Transaction
     */
    public function refund(
        Transaction $transaction,
        array $data
    ): Transaction {

        return DB::transaction(function () use (
            $transaction,
            $data
        ) {

            if ($transaction->status !== 'success') {

                throw new \Exception(
                    'Only successful transactions can be refunded.'
                );

            }

            $wallet = Wallet::where(
                'user_id',
                $transaction->user_id
            )->lockForUpdate()->first();

            if ($wallet) {

                $wallet->increment(
                    'balance',
                    $data['amount']
                );

            }

            Transaction::create([

                'user_id' => $transaction->user_id,

                'reference' => strtoupper(
                    Str::random(18)
                ),

                'gateway_reference' => null,

                'type' => 'refund',

                'amount' => $data['amount'],

                'fee' => 0,

                'total' => $data['amount'],

                'currency' => $transaction->currency,

                'payment_gateway' => $transaction->payment_gateway,

                'status' => 'success',

                'description' => $data['reason'],

                'meta' => [

                    'original_transaction' => $transaction->id,

                    'admin_id' => Auth::guard('admin')->id(),

                    'note' => $data['note'] ?? null,

                ],

                'completed_at' => now(),

            ]);

            $transaction->update([

                'status' => 'refunded',

            ]);

            return $transaction->fresh()->load('user');

        });

    }

    /**
     * Update Status
     */
    public function updateStatus(
        Transaction $transaction,
        array $data
    ): Transaction {

        $transaction->update([

            'status' => $data['status'],

            'gateway_reference' => $data['provider_reference'] ?? $transaction->gateway_reference,

            'completed_at' => $data['status'] === 'success'
                ? now()
                : $transaction->completed_at,

        ]);

        return $transaction->fresh()->load('user');
    }

    /**
     * Statistics
     */
    public function statistics(): array
    {
        return [

            'total_transactions' => Transaction::count(),

            'successful_transactions' => Transaction::where(
                'status',
                'success'
            )->count(),

            'pending_transactions' => Transaction::where(
                'status',
                'pending'
            )->count(),

            'failed_transactions' => Transaction::where(
                'status',
                'failed'
            )->count(),

            'refunded_transactions' => Transaction::where(
                'status',
                'refunded'
            )->count(),

            'total_volume' => Transaction::where(
                'status',
                'success'
            )->sum('amount'),

            'today_volume' => Transaction::whereDate(
                'created_at',
                today()
            )->sum('amount'),

        ];
    }
}