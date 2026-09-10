<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataBundle extends Model
{
    use HasFactory;

    /**
     * Mass Assignable
     */
    protected $fillable = [

        /*
        |--------------------------------------------------------------------------
        | Relations
        |--------------------------------------------------------------------------
        */

        'country_id',

        'network_id',

        /*
        |--------------------------------------------------------------------------
        | Provider
        |--------------------------------------------------------------------------
        */

        'provider',

        'provider_bundle_id',

        /*
        |--------------------------------------------------------------------------
        | Bundle
        |--------------------------------------------------------------------------
        */

        'name',

        'size',

        'validity_days',

        /*
        |--------------------------------------------------------------------------
        | Pricing
        |--------------------------------------------------------------------------
        */

        'provider_price',

        'selling_price',

        'commission',

        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        'sort_order',

        'is_active',

    ];

    /**
     * Casts
     */
    protected $casts = [

        'provider_price' => 'decimal:2',

        'selling_price' => 'decimal:2',

        'commission' => 'decimal:2',

        'is_active' => 'boolean',

    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function country()
    {
        return $this->belongsTo(
            Country::class
        );
    }

    public function network()
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

    public function scopeActive($query)
    {
        return $query->where(
            'is_active',
            true
        );
    }
} 