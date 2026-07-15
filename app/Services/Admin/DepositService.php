<?php

namespace App\Services\Admin;

use App\Models\Deposit;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DepositService
{
    /**
     * Deposit List
     */
    public function index(array $filters)
    {
        return Deposit::query()

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
                                'gateway_reference',
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
                $filters['gateway'] ?? null,
                fn($q, $gateway) => $q->where('gateway', $gateway)
            )

            ->when(
                $filters['payment_method'] ?? null,
                fn($q, $method) => $q->where('payment_method', $method)
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

            ->latest()

            ->paginate(
                $filters['per_page'] ?? 20
            );
    }

    /**
     * Show Deposit
     */
    public function show(Deposit $deposit): Deposit
    {
        return $deposit->load([
            'user',
            'wallet',
        ]);
    }

    /**
     * Approve Deposit
     */
    public function approve(
        Deposit $deposit,
        array $data
    ): Deposit {

        return DB::transaction(function () use (
            $deposit,
            $data
        ) {

            if ($deposit->status !== 'pending') {

                throw new \Exception(
                    'Only pending deposits can be approved.'
                );

            }

            if ($data['credit_wallet']) {

                $wallet = Wallet::where(
                    'id',
                    $deposit->wallet_id
                )->lockForUpdate()->first();

                if ($wallet) {

                    $wallet->increment(
                        'balance',
                        $deposit->amount
                    );

                }

            }

            Transaction::create([

                'user_id' => $deposit->user_id,

                'reference' => $deposit->reference,

                'type' => 'deposit',

                'amount' => $deposit->amount,

                'fee' => $deposit->fee,

                'total' => $deposit->total,

                'currency' => $deposit->currency,

                'status' => 'success',

                'payment_gateway' => $deposit->gateway,

                'description' => 'Wallet Deposit',

            ]);

            $deposit->update([

                'status' => 'completed',

                'gateway_reference' => $data['gateway_reference'],

                'provider_status' => $data['provider_status'],

                'provider_response' => $data['provider_response'] ?? null,

                'approved_by' => Auth::guard('admin')->id(),

                'approved_at' => now(),

                'completed_at' => now(),

                'admin_note' => $data['note'] ?? null,

            ]);

            return $deposit->fresh()->load([
                'user',
                'wallet',
            ]);

        });

    }

    /**
     * Reject Deposit
     */
    public function reject(
        Deposit $deposit,
        array $data
    ): Deposit {

        $deposit->update([

            'status' => 'rejected',

            'reject_reason' => $data['reason'],

            'reject_code' => $data['reject_code'],

            'provider_response' => $data['provider_response'] ?? null,

            'admin_note' => $data['note'] ?? null,

        ]);

        return $deposit->fresh()->load([
            'user',
            'wallet',
        ]);
    }

    /**
     * Cancel Deposit
     */
    public function cancel(
        Deposit $deposit,
        array $data
    ): Deposit {

        $deposit->update([

            'status' => 'cancelled',

            'cancel_reason' => $data['reason'],

            'cancel_code' => $data['cancel_code'],

            'cancelled_at' => now(),

            'admin_note' => $data['note'] ?? null,

        ]);

        return $deposit->fresh()->load([
            'user',
            'wallet',
        ]);
    }

    /**
     * Statistics
     */
    public function statistics(): array
    {
        return [

            'total_deposits' => Deposit::count(),

            'pending' => Deposit::where(
                'status',
                'pending'
            )->count(),

            'completed' => Deposit::where(
                'status',
                'completed'
            )->count(),

            'failed' => Deposit::where(
                'status',
                'failed'
            )->count(),

            'cancelled' => Deposit::where(
                'status',
                'cancelled'
            )->count(),

            'total_volume' => Deposit::sum(
                'amount'
            ),

            'today_volume' => Deposit::whereDate(
                'created_at',
                today()
            )->sum('amount'),

        ];
    }
}