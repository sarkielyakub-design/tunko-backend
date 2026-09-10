<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Recipient extends Model
{
    protected $fillable = [

        'user_id',

        'name',

        'phone',

        'email',

        'country',

        'currency',

        'bank_name',

        'account_number',

        'wallet_number',

    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}