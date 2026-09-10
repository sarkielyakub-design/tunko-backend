<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KycResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Identity
            |--------------------------------------------------------------------------
            */

            'id' => $this->id,

            /*
            |--------------------------------------------------------------------------
            | User
            |--------------------------------------------------------------------------
            */

            'user' => [

                'id' => $this->user?->id,

                'name' => $this->user?->full_name,

                'email' => $this->user?->email,

                'phone' => $this->user?->phone,

                'country' => $this->user?->country,

                'is_verified' => (bool) $this->user?->is_verified,

            ],

            /*
            |--------------------------------------------------------------------------
            | Personal Information
            |--------------------------------------------------------------------------
            */

            'first_name' => $this->first_name,

            'last_name' => $this->last_name,

            'middle_name' => $this->middle_name,

            'date_of_birth' => optional(
                $this->date_of_birth
            )?->toDateString(),

            'gender' => $this->gender,

            'marital_status' => $this->marital_status,

            'nationality' => $this->nationality,

            'occupation' => $this->occupation,

            'source_of_income' => $this->source_of_income,

            /*
            |--------------------------------------------------------------------------
            | KYC
            |--------------------------------------------------------------------------
            */

            'level' => $this->level,

            'status' => $this->status,

            'is_verified' => (bool) $this->is_verified,

            /*
            |--------------------------------------------------------------------------
            | Document
            |--------------------------------------------------------------------------
            */

            'document_type' => $this->document_type,

            'document_country' => $this->document_country,

            'id_type' => $this->id_type,

            'id_number' => $this->id_number,

            /*
            |--------------------------------------------------------------------------
            | Uploaded Documents
            |--------------------------------------------------------------------------
            */

            'id_front' => $this->id_front,

            'id_back' => $this->id_back,

            'selfie' => $this->selfie,

            /*
            |--------------------------------------------------------------------------
            | Verification
            |--------------------------------------------------------------------------
            */

            'verification_provider' => $this->verification_provider,

            'provider_reference' => $this->provider_reference,

            /*
            |--------------------------------------------------------------------------
            | Review
            |--------------------------------------------------------------------------
            */

            'reviewed_by' => [

                'id' => $this->reviewer?->id,

                'name' => $this->reviewer?->name,

            ],

            'admin_note' => $this->admin_note,

            'rejection_reason' => $this->rejection_reason,

            'reject_code' => $this->reject_code,

            /*
            |--------------------------------------------------------------------------
            | Dates
            |--------------------------------------------------------------------------
            */

            'reviewed_at' => optional(
                $this->reviewed_at
            )?->toDateTimeString(),

            'approved_at' => optional(
                $this->approved_at
            )?->toDateTimeString(),

            'rejected_at' => optional(
                $this->rejected_at
            )?->toDateTimeString(),

            'created_at' => optional(
                $this->created_at
            )?->toDateTimeString(),

            'updated_at' => optional(
                $this->updated_at
            )?->toDateTimeString(),

        ];
    }
}