<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Withdrawal extends Model
{
    use HasFactory;

    /**
     * -------------------------------------------------
     * Mass Assignable
     * -------------------------------------------------
     */
    protected $fillable = [
        'user_id',
        'wallet_id',
        'reference',

        'amount',
        'fee',
        'total',
        'currency',

        'provider',
        'provider_reference',
        'provider_status',
        'provider_response',

        'status',

        'reject_reason',
        'reject_code',

        'cancel_reason',
        'cancel_code',

        'approved_by',
        'approved_at',
        'cancelled_at',

        'retry_count',
        'last_retry_at',

        'admin_note',
        'remark',
    ];

    /**
     * -------------------------------------------------
     * Attribute Casting
     * -------------------------------------------------
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'total' => 'decimal:2',

        'approved_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'last_retry_at' => 'datetime',

        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * -------------------------------------------------
     * Boot
     * -------------------------------------------------
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($withdrawal) {

            if (empty($withdrawal->reference)) {

                do {

                    $reference =
                        'WD' .
                        now()->format('YmdHis') .
                        strtoupper(Str::random(4));

                } while (
                    self::where('reference', $reference)->exists()
                );

                $withdrawal->reference = $reference;
            }

            if (empty($withdrawal->status)) {
                $withdrawal->status = 'pending';
            }

            if ($withdrawal->retry_count === null) {
                $withdrawal->retry_count = 0;
            }
        });
    }

    /**
     * -------------------------------------------------
     * User
     * -------------------------------------------------
     */
    public function user()
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }

    /**
     * -------------------------------------------------
     * Wallet
     * -------------------------------------------------
     */
    public function wallet()
    {
        return $this->belongsTo(
            Wallet::class,
            'wallet_id'
        );
    }

    /**
     * -------------------------------------------------
     * Admin
     * -------------------------------------------------
     */
    public function approver()
    {
        return $this->belongsTo(
            Admin::class,
            'approved_by'
        );
    }

    /**
     * -------------------------------------------------
     * Accessors
     * -------------------------------------------------
     */
    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2);
    }

    public function getFormattedFeeAttribute()
    {
        return number_format($this->fee, 2);
    }

    public function getFormattedTotalAttribute()
    {
        return number_format($this->total, 2);
    }

    /**
     * -------------------------------------------------
     * Scopes
     * -------------------------------------------------
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}