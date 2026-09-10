<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    /**
     * Mass Assignable
     */
    protected $fillable = [

        'admin_id',

        'module',

        'action',

        'description',

        'ip_address',

        'user_agent',

        'old_values',

        'new_values',

    ];

    /**
     * Casts
     */
    protected $casts = [

        'old_values' => 'array',

        'new_values' => 'array',

    ];

    /**
     * Admin Relationship
     */
    public function admin()
    {
        return $this->belongsTo(
            Admin::class
        );
    }
}