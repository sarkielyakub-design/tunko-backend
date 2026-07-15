<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use App\Models\Wallet;
use App\Models\Kyc;
use App\Models\Transaction;
use App\Models\Beneficiary;
use App\Models\Card;
use App\Models\Recipient;
use App\Models\WalletTransaction;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Always eager load wallet
     */
    protected $with = [
        'wallet',
    ];

    /**
     * Mass Assignable
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'email',
        'phone',
        'country',
        'password',
        'transaction_pin',
        'referral_code',
        'otp',
        'otp_expires_at',
        'is_verified',
        'is_active',
    ];

    /**
     * Hidden Attributes
     */
    protected $hidden = [
        'password',
        'remember_token',
        'transaction_pin',
        'otp',
    ];

    /**
     * Attribute Casting
     */
    protected $casts = [
        'password' => 'hashed',
        'email_verified_at' => 'datetime',
        'otp_expires_at' => 'datetime',
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Full Name Accessor
     */
    public function getFullNameAttribute(): string
    {
        return trim(
            "{$this->first_name} {$this->last_name}"
        );
    }

    /**
     * Wallet
     */
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    /**
     * KYC
     */
    public function kyc()
    {
        return $this->hasOne(Kyc::class);
    }

    /**
     * Transactions
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Beneficiaries
     */
    public function beneficiaries()
    {
        return $this->hasMany(Beneficiary::class);
    }

    /**
     * Cards
     */
    public function cards()
    {
        return $this->hasMany(Card::class);
    }

    /**
     * Recipients
     */
    public function recipients()
    {
        return $this->hasMany(
            Recipient::class,
            'user_id'
        );
    }

    /**
     * Wallet Transactions
     */
    public function walletTransactions()
    {
        return $this->hasMany(
            WalletTransaction::class,
            'user_id'
        );
    }

    /**
     * Check if user is verified
     */
    public function isVerified(): bool
    {
        return $this->is_verified;
    }

    /**
     * Check if account is active
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }
}