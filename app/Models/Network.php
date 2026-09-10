<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Network extends Model
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

        /*
        |--------------------------------------------------------------------------
        | Network
        |--------------------------------------------------------------------------
        */

        'name',

        'slug',

        'code',

        'provider',

        /*
        |--------------------------------------------------------------------------
        | Branding
        |--------------------------------------------------------------------------
        */

        'logo',

        'color',

        /*
        |--------------------------------------------------------------------------
        | Services
        |--------------------------------------------------------------------------
        */

        'airtime_enabled',

        'data_enabled',

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

        'airtime_enabled' => 'boolean',

        'data_enabled' => 'boolean',

        'is_active' => 'boolean',

    ];

    /**
     * Auto Slug
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($network) {

            if (empty($network->slug)) {

                $network->slug = Str::slug(

                    $network->name

                );

            }

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Country
     */
    public function country()
    {
        return $this->belongsTo(
            Country::class
        );
    }

    /**
     * Data Bundles
     */
    public function dataBundles()
    {
        return $this->hasMany(
            DataBundle::class
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

    public function scopeAirtime($query)
    {
        return $query->where(
            'airtime_enabled',
            true
        );
    }

    public function scopeData($query)
    {
        return $query->where(
            'data_enabled',
            true
        );
    }
}