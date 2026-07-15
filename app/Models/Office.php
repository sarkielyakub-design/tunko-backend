<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Office extends Model
{
    use HasFactory;

    /**
     * Mass Assignable
     */
    protected $fillable = [

        /*
        |--------------------------------------------------------------------------
        | Basic
        |--------------------------------------------------------------------------
        */

        'name',

        'slug',

        'country',

        'state',

        'city',

        /*
        |--------------------------------------------------------------------------
        | Contact
        |--------------------------------------------------------------------------
        */

        'email',

        'phone',

        'whatsapp',

        /*
        |--------------------------------------------------------------------------
        | Address
        |--------------------------------------------------------------------------
        */

        'address',

        'latitude',

        'longitude',

        /*
        |--------------------------------------------------------------------------
        | Business
        |--------------------------------------------------------------------------
        */

        'timezone',

        'currency',

        'is_head_office',

        'is_active',

        'sort_order',

        /*
        |--------------------------------------------------------------------------
        | SEO
        |--------------------------------------------------------------------------
        */

        'meta_title',

        'meta_description',

    ];

    /**
     * Attribute Casting
     */
    protected $casts = [

        'is_head_office' => 'boolean',

        'is_active' => 'boolean',

        'latitude' => 'decimal:7',

        'longitude' => 'decimal:7',

    ];

    /**
     * Auto Generate Slug
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($office) {

            if (empty($office->slug)) {

                $office->slug = Str::slug(
                    $office->name . '-' . $office->city
                );

            }

        });

    }

    /**
     * Office Full Address
     */
    public function getFullAddressAttribute(): string
    {
        return collect([

            $this->address,

            $this->city,

            $this->state,

            $this->country,

        ])

        ->filter()

        ->implode(', ');
    }

    /**
     * Google Maps URL
     */
    public function getGoogleMapsUrlAttribute(): ?string
    {
        if (
            !$this->latitude ||
            !$this->longitude
        ) {
            return null;
        }

        return sprintf(
            'https://maps.google.com/?q=%s,%s',
            $this->latitude,
            $this->longitude
        );
    }

    /**
     * Scope Active Offices
     */
    public function scopeActive($query)
    {
        return $query->where(
            'is_active',
            true
        );
    }

    /**
     * Scope Head Office
     */
    public function scopeHeadOffice($query)
    {
        return $query->where(
            'is_head_office',
            true
        );
    }
}