<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DataPurchase extends Model
{
    use HasFactory;

    /**
     * Mass Assignable
     */
    protected $fillable = [

        /*
        |--------------------------------------------------------------------------
        | Relationships
        |--------------------------------------------------------------------------
        */

        'user_id',

        'country_id',

        /*
        |--------------------------------------------------------------------------
        | Purchase
        |--------------------------------------------------------------------------
        */

        'reference',

        'network_id',

        'bundle_id',

        'phone',

        'network',

        'bundle',

        /*
        |--------------------------------------------------------------------------
        | Financial
        |--------------------------------------------------------------------------
        */

        'amount',

        'currency',

        /*
        |--------------------------------------------------------------------------
        | Provider
        |--------------------------------------------------------------------------
        */

        'provider',

        'provider_reference',

        'provider_response',

        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        'status',

    ];

    /**
     * Attribute Casting
     */
    protected $casts = [

        'amount' => 'decimal:2',

    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }

    public function country()
    {
        return $this->belongsTo(
            Country::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}