<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    protected $fillable = [
        'user_id',
        'card_holder',
        'card_number',
        'last_four',
        'brand',
        'expiry_month',
        'expiry_year',
        'is_default',
        'is_frozen',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_frozen' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}