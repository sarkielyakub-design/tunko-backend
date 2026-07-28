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

        'document_type',
        'document_country',

        'id_front',
        'id_back',
        'selfie',

        'level',

        'status',
        'is_verified',

        'verification_provider',
        'provider_reference',

        'reviewed_by',
        'reviewed_at',

        'approved_at',
        'rejected_at',

        'admin_note',
        'rejection_reason',
        'reject_code',
    ];

    protected $casts = [
        'date_of_birth' => 'date',

        'is_verified' => 'boolean',

        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    /**
     * User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Admin Reviewer
     */
    public function reviewer()
    {
        return $this->belongsTo(
            Admin::class,
            'reviewed_by'
        );
    }

    /**
     * Pending Scope
     */
    public function scopePending($query)
    {
        return $query->where(
            'status',
            'pending'
        );
    }

    /**
     * Approved Scope
     */
    public function scopeApproved($query)
    {
        return $query->where(
            'status',
            'approved'
        );
    }

    /**
     * Rejected Scope
     */
    public function scopeRejected($query)
    {
        return $query->where(
            'status',
            'rejected'
        );
    }

    /**
     * Under Review Scope
     */
    public function scopeUnderReview($query)
    {
        return $query->where(
            'status',
            'under_review'
        );
    }
}