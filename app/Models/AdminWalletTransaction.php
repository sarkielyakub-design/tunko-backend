<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminWalletTransaction extends Model
{
    protected $fillable = [
        'admin_wallet_id',
        'reference',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'currency',
        'source',
        'description',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function wallet()
    {
        return $this->belongsTo(AdminWallet::class, 'admin_wallet_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }
}