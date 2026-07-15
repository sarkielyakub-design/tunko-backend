<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Airtime extends Model
{
    use HasFactory;

    protected $fillable = [

        'user_id',

        'reference',

        'country_id',

        'country',

        'network',

        'phone',

        'amount',

        'currency',

        'provider',

        'provider_reference',

        'status',

        'remark',

    ];

    protected $casts = [

        'amount' => 'decimal:2',

    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}