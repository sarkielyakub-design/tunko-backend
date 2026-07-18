<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WalletTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'sender_id',
        'recipient_id',
        'sender_wallet_id',
        'recipient_wallet_id',
        'amount',
        'fee',
        'total',
        'currency',
        'status',
        'description',
        'completed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'total' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function senderWallet()
    {
        return $this->belongsTo(Wallet::class, 'sender_wallet_id');
    }

    public function recipientWallet()
    {
        return $this->belongsTo(Wallet::class, 'recipient_wallet_id');
    }
}