<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Transfer extends Model
{
    use HasFactory;

    /**
     * -------------------------------------------------
     * Mass Assignable
     * -------------------------------------------------
     */
    protected $fillable = [
        'user_id',
        'recipient_id',
        'reference',
        'destination_country',
        'destination_currency',
        'send_amount',
        'receive_amount',
        'exchange_rate',
        'fee',
        'total',
        'status',
        'remark',
    ];

    /**
     * -------------------------------------------------
     * Attribute Casting
     * -------------------------------------------------
     */
    protected $casts = [
        'send_amount' => 'decimal:2',
        'receive_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'fee' => 'decimal:2',
        'total' => 'decimal:2',
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

        static::creating(function ($transfer) {

            if (empty($transfer->reference)) {

                do {

                    $reference =
                        'TNK' .
                        now()->format('YmdHis') .
                        strtoupper(Str::random(4));

                } while (
                    self::where('reference', $reference)->exists()
                );

                $transfer->reference = $reference;
            }

            if (empty($transfer->status)) {
                $transfer->status = 'pending';
            }
        });
    }

    /**
     * -------------------------------------------------
     * Sender
     * -------------------------------------------------
     */
    public function sender()
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }

    /**
     * -------------------------------------------------
     * Recipient User
     * -------------------------------------------------
     */
    public function recipient()
    {
        return $this->belongsTo(
            User::class,
            'recipient_id'
        );
    }

    /**
     * -------------------------------------------------
     * Accessors
     * -------------------------------------------------
     */
    public function getFormattedSendAmountAttribute()
    {
        return number_format(
            $this->send_amount,
            2
        );
    }

    public function getFormattedReceiveAmountAttribute()
    {
        return number_format(
            $this->receive_amount,
            2
        );
    }

    public function getFormattedFeeAttribute()
    {
        return number_format(
            $this->fee,
            2
        );
    }

    public function getFormattedTotalAttribute()
    {
        return number_format(
            $this->total,
            2
        );
    }

    /**
     * -------------------------------------------------
     * Status Helpers
     * -------------------------------------------------
     */
    public function getIsPendingAttribute()
    {
        return $this->status === 'pending';
    }

    public function getIsCompletedAttribute()
    {
        return $this->status === 'completed';
    }

    public function getIsFailedAttribute()
    {
        return $this->status === 'failed';
    }

    /**
     * -------------------------------------------------
     * Scopes
     * -------------------------------------------------
     */
    public function scopeCompleted($query)
    {
        return $query->where(
            'status',
            'completed'
        );
    }

    public function scopePending($query)
    {
        return $query->where(
            'status',
            'pending'
        );
    }

    public function scopeFailed($query)
    {
        return $query->where(
            'status',
            'failed'
        );
    }
}