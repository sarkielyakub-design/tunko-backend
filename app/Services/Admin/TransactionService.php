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
   /**
 * Transaction List
 */
public function index(array $filters)
{
    $allowedSorts = [
        'created_at',
        'amount',
        'status',
        'type',
        'currency',
        'reference',
    ];

    $sort = in_array(
        $filters['sort'] ?? '',
        $allowedSorts
    ) ? $filters['sort'] : 'created_at';

    $direction = strtolower(
        $filters['direction'] ?? 'desc'
    ) === 'asc'
        ? 'asc'
        : 'desc';

    return Transaction::query()

        ->with([
            'user',
        ])

        ->when(
            !empty($filters['search']),
            function ($query) use ($filters) {

                $search = trim($filters['search']);

                $query->where(function ($q) use ($search) {

                    $q->where('reference', 'like', "%{$search}%")
                        ->orWhere('gateway_reference', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
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
            !empty($filters['status']),
            fn ($q) => $q->where('status', $filters['status'])
        )

        ->when(
            !empty($filters['type']),
            fn ($q) => $q->where('type', $filters['type'])
        )

        ->when(
            !empty($filters['payment_gateway']),
            fn ($q) => $q->where(
                'payment_gateway',
                $filters['payment_gateway']
            )
        )

        ->when(
            !empty($filters['currency']),
            fn ($q) => $q->where(
                'currency',
                $filters['currency']
            )
        )

        ->when(
            !empty($filters['user_id']),
            fn ($q) => $q->where(
                'user_id',
                $filters['user_id']
            )
        )

        ->when(
            !empty($filters['min_amount']),
            fn ($q) => $q->where(
                'amount',
                '>=',
                $filters['min_amount']
            )
        )

        ->when(
            !empty($filters['max_amount']),
            fn ($q) => $q->where(
                'amount',
                '<=',
                $filters['max_amount']
            )
        )

        ->when(
            !empty($filters['from_date']),
            fn ($q) => $q->whereDate(
                'created_at',
                '>=',
                $filters['from_date']
            )
        )

        ->when(
            !empty($filters['to_date']),
            fn ($q) => $q->whereDate(
                'created_at',
                '<=',
                $filters['to_date']
            )
        )

        ->orderBy($sort, $direction)

        ->paginate(
            $filters['per_page'] ?? 20
        )

        ->withQueryString();
}
 /**
 * Show Transaction
 */
public function show(Transaction $transaction): Transaction
{
    return $transaction->load([
        'user',
    ]);
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

        // Refresh and lock transaction
        $transaction = Transaction::whereKey($transaction->id)
            ->lockForUpdate()
            ->firstOrFail();

        if ($transaction->status !== 'success') {
            throw new \Exception(
                'Only successful transactions can be refunded.'
            );
        }

        if ($transaction->type === 'refund') {
            throw new \Exception(
                'This transaction is already a refund.'
            );
        }

        if ($transaction->status === 'refunded') {
            throw new \Exception(
                'Transaction has already been refunded.'
            );
        }

        $refundAmount = $data['amount'];

        if ($refundAmount <= 0) {
            throw new \Exception(
                'Refund amount must be greater than zero.'
            );
        }

        if ($refundAmount > $transaction->amount) {
            throw new \Exception(
                'Refund amount cannot exceed the original transaction.'
            );
        }

        $wallet = Wallet::where(
            'user_id',
            $transaction->user_id
        )
        ->lockForUpdate()
        ->first();

        if (! $wallet) {
            throw new \Exception(
                'Customer wallet not found.'
            );
        }

        // Credit wallet
        $wallet->increment(
            'balance',
            $refundAmount
        );

        // Create refund transaction
        Transaction::create([

            'user_id' => $transaction->user_id,

            'reference' => strtoupper(
                Str::random(18)
            ),

            'gateway_reference' => null,

            'type' => 'refund',

            'amount' => $refundAmount,

            'fee' => 0,

            'total' => $refundAmount,

            'currency' => $transaction->currency,

            'payment_gateway' => $transaction->payment_gateway,

            'status' => 'success',

            'description' => $data['reason'],

            'meta' => [
                'original_transaction' => $transaction->id,
                'admin_id' => Auth::guard('admin')->id(),
                'admin_name' => optional(Auth::guard('admin')->user())->name,
                'note' => $data['note'] ?? null,
                'refunded_at' => now(),
            ],

            'completed_at' => now(),

        ]);

        // Mark original transaction
        $transaction->update([
            'status' => 'refunded',
        ]);

        return $transaction->fresh()->load([
            'user',
        ]);

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
 * Dashboard Statistics
 */
public function statistics(): array
{
    $successful = Transaction::where('status', 'success');

    return [

        // Cards
        'total_transactions' => Transaction::count(),

        'successful_transactions' => Transaction::where(
            'status',
            'success'
        )->count(),

        'pending_transactions' => Transaction::where(
            'status',
            'pending'
        )->count(),

        'processing_transactions' => Transaction::where(
            'status',
            'processing'
        )->count(),

        'failed_transactions' => Transaction::where(
            'status',
            'failed'
        )->count(),

        'refunded_transactions' => Transaction::where(
            'status',
            'refunded'
        )->count(),

        'cancelled_transactions' => Transaction::where(
            'status',
            'cancelled'
        )->count(),

        // Volumes
        'total_volume' => $successful->sum('amount'),

        'today_volume' => Transaction::where(
            'status',
            'success'
        )->whereDate(
            'created_at',
            today()
        )->sum('amount'),

        'week_volume' => Transaction::where(
            'status',
            'success'
        )->whereBetween(
            'created_at',
            [
                now()->startOfWeek(),
                now()->endOfWeek(),
            ]
        )->sum('amount'),

        'month_volume' => Transaction::where(
            'status',
            'success'
        )->whereMonth(
            'created_at',
            now()->month
        )->whereYear(
            'created_at',
            now()->year
        )->sum('amount'),

        // Fees
        'total_fees' => Transaction::where(
            'status',
            'success'
        )->sum('fee'),

        // Averages
        'average_transaction' => round(
            Transaction::where('status', 'success')
                ->avg('amount'),
            2
        ),

        // Today's Activity
        'today_transactions' => Transaction::whereDate(
            'created_at',
            today()
        )->count(),

        'today_successful' => Transaction::whereDate(
            'created_at',
            today()
        )->where(
            'status',
            'success'
        )->count(),

    ];

}
}