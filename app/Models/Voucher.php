<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        return $this->belongsTo(
            User::class
        );
    }

    public function network(): BelongsTo
    {
        return $this->belongsTo(
            Network::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeAvailable($query)
    {
        return $query->where(
            'status',
            'available'
        );
    }

    public function scopeSold($query)
    {
        return $query->where(
            'status',
            'sold'
        );
    }

    public function scopeAirtime($query)
    {
        return $query->where(
            'type',
            'airtime'
        );
    }

    public function scopeData($query)
    {
        return $query->where(
            'type',
            'data'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    public function isSold(): bool
    {
        return $this->status === 'sold';
    }
}
