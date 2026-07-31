<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deposit extends Model
{
    protected $fillable = [
        'user_id',
        'wallet_id',

        'reference',
        'gateway_reference',

        'amount',
        'fee',
        'total',
        'currency',

        'gateway',
        'payment_method',

        'provider_status',
        'provider_response',

        'status',

        'approved_by',
        'approved_at',
        'completed_at',
        'cancelled_at',

        'reject_reason',
        'reject_code',

        'cancel_reason',
        'cancel_code',

        'admin_note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'total' => 'decimal:2',

        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Deposit owner
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Wallet that receives the deposit
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    /**
     * Admin that approved the deposit
     */
    public function approve(Deposit $deposit, array $data): Deposit
{
    return DB::transaction(function () use ($deposit, $data) {

        $deposit->refresh();

        if ($deposit->status !== 'pending') {
            throw new \Exception('Only pending deposits can be approved.');
        }

        $adminWallet = AdminWallet::active()
            ->lockForUpdate()
            ->first();

        if (!$adminWallet) {
            throw new \Exception('No active admin wallet found.');
        }

        if (!$adminWallet->hasSufficientBalance($deposit->amount)) {
            throw new \Exception('Insufficient admin wallet balance.');
        }

        $userWallet = Wallet::lockForUpdate()->find($deposit->wallet_id);

        if (!$userWallet) {
            $userWallet = Wallet::where('user_id', $deposit->user_id)
                ->lockForUpdate()
                ->first();
        }

        if (!$userWallet) {
            throw new \Exception('User wallet not found.');
        }

        $before = $adminWallet->balance;

        $adminWallet->debit($deposit->amount);

        $adminWallet->refresh();

        $after = $adminWallet->balance;

        AdminWalletTransaction::create([

            'admin_wallet_id' => $adminWallet->id,

            'reference' => 'AWT-'.Str::upper(Str::random(12)),

            'type' => 'debit',

            'amount' => $deposit->amount,

            'balance_before' => $before,

            'balance_after' => $after,

            'currency' => $deposit->currency,

            'source' => 'manual_deposit',

            'description' => 'Manual bank deposit approved',

            'created_by' => Auth::guard('admin')->id(),

        ]);

        $userWallet->increment('balance', $deposit->amount);

        Transaction::create([

            'user_id' => $deposit->user_id,

            'reference' => $deposit->reference,

            'type' => 'deposit',

            'amount' => $deposit->amount,

            'fee' => $deposit->fee,

            'total' => $deposit->amount,

            'status' => 'completed',

            'description' => 'Manual Wallet Deposit',

            'meta' => [
                'wallet_id' => $userWallet->id,
                'gateway' => 'manual',
                'payment_method' => $deposit->payment_method,
            ],

        ]);

       $deposit->update([

    'status' => 'failed',

    'provider_status' => 'rejected',

    'provider_response' => $data['reason'],

    'reject_reason' => $data['reason'],

    'reject_code' => $data['reject_code'],

    'admin_note' => $data['note'] ?? null,

]);

        return $deposit->fresh()->load([
            'user',
            'wallet',
        ]);

    });
}

    /**
     * Related transactions (optional)
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'reference', 'reference');
    }

    /**
     * Timeline accessor
     */
    public function getTimelineAttribute(): array
    {
        return [
            [
                'title' => 'Deposit Created',
                'status' => 'completed',
                'date' => optional($this->created_at)->toDateTimeString(),
            ],

            $this->approved_at ? [
                'title' => 'Approved',
                'status' => 'completed',
                'date' => $this->approved_at->toDateTimeString(),
            ] : null,

            $this->completed_at ? [
                'title' => 'Completed',
                'status' => 'completed',
                'date' => $this->completed_at->toDateTimeString(),
            ] : null,

            $this->cancelled_at ? [
                'title' => 'Cancelled',
                'status' => 'cancelled',
                'date' => $this->cancelled_at->toDateTimeString(),
            ] : null,
        ];
    }
}