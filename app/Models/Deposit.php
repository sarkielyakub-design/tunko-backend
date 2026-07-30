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
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'approved_by');
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