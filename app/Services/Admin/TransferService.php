<?php

namespace App\Services\Admin;

use App\Models\Transfer;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransferService
{
    /**
     * List Transfers
     */
    public function index(array $filters)
    {
        return Transfer::query()

            ->with([
                'user',
                'recipient',
            ])

            ->when(
                $filters['search'] ?? null,
                function ($query, $search) {

                    $query->where(function ($q) use ($search) {

                        $q->where('reference', 'like', "%{$search}%")

                            ->orWhere('provider_reference', 'like', "%{$search}%")

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
                $filters['sender_currency'] ?? null,
                fn($q, $currency) => $q->where('sender_currency', $currency)
            )

            ->when(
                $filters['recipient_currency'] ?? null,
                fn($q, $currency) => $q->where('recipient_currency', $currency)
            )

            ->latest()

            ->paginate(
                $filters['per_page'] ?? 20
            );
    }

    /**
     * Transfer Details
     */
    public function show(
        Transfer $transfer
    ): Transfer {

        return $transfer->load([
            'user',
            'recipient',
        ]);

    }

    /**
     * Approve Transfer
     */
    public function approve(
        Transfer $transfer,
        array $data
    ): Transfer {

        return DB::transaction(function () use (
            $transfer,
            $data
        ) {

            $transfer->update([

                'status' => 'approved',

                'provider' => $data['provider'],

                'provider_reference' => $data['provider_reference'] ?? null,

                'exchange_rate' => $data['exchange_rate'],

                'recipient_amount' => $data['recipient_amount'],

                'approved_by' => Auth::guard('admin')->id(),

                'approved_at' => now(),

                'admin_note' => $data['note'] ?? null,

            ]);

            return $transfer->fresh()
                ->load([
                    'user',
                    'recipient',
                ]);

        });

    }

    /**
     * Reject Transfer
     */
    public function reject(
        Transfer $transfer,
        array $data
    ): Transfer {

        return DB::transaction(function () use (
            $transfer,
            $data
        ) {

            $transfer->update([

                'status' => 'rejected',

                'reject_reason' => $data['reason'],

                'reject_code' => $data['reject_code'],

                'admin_note' => $data['note'] ?? null,

            ]);

            if ($data['refund_wallet']) {

                $wallet = Wallet::where(
                    'user_id',
                    $transfer->user_id
                )->lockForUpdate()->first();

                if ($wallet) {

                    $wallet->increment(
                        'balance',
                        $transfer->total
                    );

                }

            }

            return $transfer->fresh()
                ->load([
                    'user',
                    'recipient',
                ]);

        });

    }

    /**
     * Cancel Transfer
     */
    public function cancel(
        Transfer $transfer,
        array $data
    ): Transfer {

        return DB::transaction(function () use (
            $transfer,
            $data
        ) {

            $transfer->update([

                'status' => 'cancelled',

                'cancel_reason' => $data['reason'],

                'admin_note' => $data['note'] ?? null,

                'cancelled_at' => now(),

            ]);

            if ($data['refund_wallet']) {

                $wallet = Wallet::where(
                    'user_id',
                    $transfer->user_id
                )->lockForUpdate()->first();

                if ($wallet) {

                    $wallet->increment(
                        'balance',
                        $transfer->total
                    );

                }

            }

            return $transfer->fresh()
                ->load([
                    'user',
                    'recipient',
                ]);

        });

    }

    /**
     * Retry Transfer
     */
    public function retry(
        Transfer $transfer,
        array $data
    ): Transfer {

        return DB::transaction(function () use (
            $transfer,
            $data
        ) {

            $transfer->increment(
                'retry_count'
            );

            $transfer->update([

                'status' => 'processing',

                'provider' => $data['provider']
                    ?? $transfer->provider,

                'last_retry_at' => now(),

                'admin_note' => $data['note'] ?? null,

            ]);

            return $transfer->fresh()
                ->load([
                    'user',
                    'recipient',
                ]);

        });

    }

    /**
     * Statistics
     */
    public function statistics(): array
    {
        return [

            'total_transfers' => Transfer::count(),

            'pending' => Transfer::where(
                'status',
                'pending'
            )->count(),

            'processing' => Transfer::where(
                'status',
                'processing'
            )->count(),

            'completed' => Transfer::where(
                'status',
                'completed'
            )->count(),

            'failed' => Transfer::where(
                'status',
                'failed'
            )->count(),

            'cancelled' => Transfer::where(
                'status',
                'cancelled'
            )->count(),

            'total_volume' => Transfer::sum(
                'amount'
            ),

            'today_volume' => Transfer::whereDate(
                'created_at',
                today()
            )->sum('amount'),

        ];
    }
}