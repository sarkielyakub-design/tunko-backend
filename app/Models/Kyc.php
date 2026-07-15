<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kyc extends Model
{
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'middle_name',
        'date_of_birth',
        'gender',
        'marital_status',
        'nationality',
        'occupation',
        'source_of_income',
        'id_type',
        'id_number',
        'id_front',
        'id_back',
        'selfie',
        'status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}