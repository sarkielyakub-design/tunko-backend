<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'type',
        'country_code',
        'network_id',
        'network_name',
        'product_name',
        'amount',
        'currency',
        'pin',
        'status',
        'user_id',
        'purchase_reference',
        'provider',
        'provider_reference',
        'sold_at',
        'expires_at',
        'remark',
        'meta',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'sold_at' => 'datetime',
        'expires_at' => 'datetime',
        'meta' => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function network(): BelongsTo
    {
        return $this->belongsTo(Network::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', 'available');
    }

    public function scopeSold(Builder $query): Builder
    {
        return $query->where('status', 'sold');
    }

    public function scopeAirtime(Builder $query): Builder
    {
        return $query->where('type', 'airtime');
    }

    public function scopeData(Builder $query): Builder
    {
        return $query->where('type', 'data');
    }

    public function scopeForCountry(
        Builder $query,
        string $countryCode
    ): Builder {
        return $query->where(
            'country_code',
            strtoupper($countryCode)
        );
    }

    public function scopeForAmount(
        Builder $query,
        float $amount
    ): Builder {
        return $query->where(
            'amount',
            $amount
        );
    }

    public function scopeForType(
        Builder $query,
        string $type
    ): Builder {
        return $query->where(
            'type',
            strtolower($type)
        );
    }

    public function scopeNotExpired(Builder $query): Builder
    {
        return $query->where(function (Builder $query) {
            $query
                ->whereNull('expires_at')
                ->orWhere(
                    'expires_at',
                    '>',
                    now()
                );
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Availability Query
    |--------------------------------------------------------------------------
    */

    public function scopeCurrentlyAvailable(
        Builder $query
    ): Builder {
        return $query
            ->available()
            ->notExpired();
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isAvailable(): bool
    {
        return $this->status === 'available'
            && (
                is_null($this->expires_at)
                || $this->expires_at->isFuture()
            );
    }

    public function isSold(): bool
    {
        return $this->status === 'sold';
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null
            && $this->expires_at->isPast();
    }

    /*
    |--------------------------------------------------------------------------
    | Mark Voucher as Sold
    |--------------------------------------------------------------------------
    */

    public function markAsSold(
        ?int $userId = null,
        ?string $purchaseReference = null
    ): bool {
        return $this->update([
            'status' => 'sold',
            'user_id' => $userId,
            'purchase_reference' => $purchaseReference,
            'sold_at' => now(),
        ]);
    }
}