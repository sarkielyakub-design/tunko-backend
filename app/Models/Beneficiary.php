<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Beneficiary extends Model
{
    protected $fillable = [
        'user_id',
        'beneficiary_user_id',
        'nickname',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function beneficiary()
    {
        return $this->belongsTo(
            User::class,
            'beneficiary_user_id'
        );
    }
}