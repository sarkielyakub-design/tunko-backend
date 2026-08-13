<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfficeTransfer extends Model
{
    protected $fillable = [

        'reference',

        'sender_id',

        'source_office_id',

        'destination_country_id',

        'destination_city',

        'recipient_phone',

        'recipient_first_name',

        'recipient_last_name',

        'amount',

        'fee',

        'total',

        'currency',

        'fees_included',

        'reason',

        'status',

        'completed_at',

        'description',

        'meta',
    ];

    protected $casts = [

        'amount' =>
            'decimal:2',

        'fee' =>
            'decimal:2',

        'total' =>
            'decimal:2',

        'fees_included' =>
            'boolean',

        'completed_at' =>
            'datetime',

        'meta' =>
            'array',
    ];


    /*
    |--------------------------------------------------------------------------
    | Sender
    |--------------------------------------------------------------------------
    */

    public function sender(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'sender_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Source Office
    |--------------------------------------------------------------------------
    */

    public function sourceOffice(): BelongsTo
    {
        return $this->belongsTo(
            Office::class,
            'source_office_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Destination Country
    |--------------------------------------------------------------------------
    */

    public function destinationCountry(): BelongsTo
    {
        return $this->belongsTo(
            Country::class,
            'destination_country_id'
        );
    }
}