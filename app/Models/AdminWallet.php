<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminWallet extends Model
{
    protected $table = 'admin_wallets';

    protected $fillable = [
        'wallet_name',
        'wallet_number',
        'currency',
        'balance',
        'status',
        'description',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    /**
     * Scope active wallet
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Check if wallet is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check sufficient balance
     */
    public function hasSufficientBalance(float $amount): bool
    {
        return $this->balance >= $amount;
    }

    /**
     * Credit wallet
     */
    public function credit(float $amount): void
    {
        $this->increment('balance', $amount);
    }

    /**
     * Debit wallet
     */
    public function debit(float $amount): void
    {
        $this->decrement('balance', $amount);
    }
}