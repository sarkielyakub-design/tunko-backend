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
            | KYC
            |--------------------------------------------------------------------------
            */

            'level' => $this->level,

            'status' => $this->status,

            'document_type' => $this->document_type,

            'document_number' => $this->document_number,

            'document_country' => $this->document_country,

            /*
            |--------------------------------------------------------------------------
            | Documents
            |--------------------------------------------------------------------------
            */

            'front_image' => $this->front_image,

            'back_image' => $this->back_image,

            'selfie_image' => $this->selfie_image,

            'proof_of_address' => $this->proof_of_address,

            /*
            |--------------------------------------------------------------------------
            | Verification
            |--------------------------------------------------------------------------
            */

            'verification_provider' => $this->verification_provider,

            'provider_reference' => $this->provider_reference,

            'is_verified' => (bool) $this->is_verified,

            /*
            |--------------------------------------------------------------------------
            | Review
            |--------------------------------------------------------------------------
            */

            'reviewed_by' => $this->reviewer?->name,

            'rejection_reason' => $this->rejection_reason,

            'admin_note' => $this->admin_note,

            /*
            |--------------------------------------------------------------------------
            | Dates
            |--------------------------------------------------------------------------
            */

            'submitted_at' => optional(
                $this->submitted_at
            )?->toDateTimeString(),

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

        ];
    }
}