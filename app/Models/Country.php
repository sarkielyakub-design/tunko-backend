<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [

        'name',

        'iso2',

        'iso3',

        'currency',

        'currency_name',

        'flag',

        'exchange_rate',

        'is_active',

    ];

    protected $casts = [

        'exchange_rate'=>'decimal:6',

        'is_active'=>'boolean',

    ];
}