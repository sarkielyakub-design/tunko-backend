<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepositResource extends JsonResource
{
    /**
     * Transform the resource.
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

            'reference' => $this->reference,

            'gateway_reference' => $this->gateway_reference,

            /*
            |--------------------------------------------------------------------------
            | Customer
            |--------------------------------------------------------------------------
            */

            'user' => [

                'id' => $this->user?->id,

                'name' => $this->user?->full_name,

                'email' => $this->user?->email,

                'phone' => $this->user?->phone,

            ],

            /*
            |--------------------------------------------------------------------------
            | Wallet
            |--------------------------------------------------------------------------
            */

            'wallet' => [

                'id' => $this->wallet?->id,

                'wallet_number' => $this->wallet?->wallet_number,

                'currency' => $this->wallet?->currency,

            ],

            /*
            |--------------------------------------------------------------------------
            | Payment
            |--------------------------------------------------------------------------
            */

            'payment_method' => $this->payment_method,

            'gateway' => $this->gateway,

            'provider_status' => $this->provider_status,

            'provider_response' => $this->provider_response,

            /*
            |--------------------------------------------------------------------------
            | Amount
            |--------------------------------------------------------------------------
            */

            'amount' => (float) $this->amount,

            'fee' => (float) $this->fee,

            'total' => (float) $this->total,

            'currency' => $this->currency,

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            'status' => $this->status,

            /*
            |--------------------------------------------------------------------------
            | Admin
            |--------------------------------------------------------------------------
            */

            'approved_by' => $this->approved_by,

            'admin_note' => $this->admin_note,

            /*
            |--------------------------------------------------------------------------
            | Timeline
            |--------------------------------------------------------------------------
            */

            'timeline' => $this->timeline,

            /*
            |--------------------------------------------------------------------------
            | Dates
            |--------------------------------------------------------------------------
            */

            'approved_at' => optional(
                $this->approved_at
            )?->toDateTimeString(),

            'completed_at' => optional(
                $this->completed_at
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