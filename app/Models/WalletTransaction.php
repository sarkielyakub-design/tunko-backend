<?php

namespace App\Models;
 use App\Models\Wallet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WalletTransaction extends Model
{
    use HasFactory;

 protected $fillable = [
    'wallet_id',
    'user_id',
    'reference',
    'gateway_reference',
    'type',
    'amount',
    'fee',
    'total',
    'currency',
    'payment_gateway',
    'status',
    'description',
    'meta',
    'completed_at',
];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'total' => 'decimal:2',
        'meta' => 'array',
        'completed_at' => 'datetime',
    ];

    /**
     * Owner of transaction
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check success
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    /**
     * Check pending
     */
   

public function wallet()
{
    return $this->belongsTo(
        Wallet::class,
        'wallet_id'
    );
}
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Mark success
     */
    public function markSuccessful()
    {
        $this->update([
            'status' => 'success',
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark failed
     */
    public function markFailed()
    {
        $this->update([
            'status' => 'failed',
        ]);
    }
}