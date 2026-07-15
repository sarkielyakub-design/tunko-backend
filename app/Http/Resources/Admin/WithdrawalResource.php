<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WithdrawalResource extends JsonResource
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

                'balance' => (float) ($this->wallet?->balance ?? 0),

            ],

            /*
            |--------------------------------------------------------------------------
            | Bank Details
            |--------------------------------------------------------------------------
            */

            'bank' => [

                'bank_name' => $this->bank_name,

                'bank_code' => $this->bank_code,

                'account_name' => $this->account_name,

                'account_number' => $this->account_number,

            ],

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
            | Provider
            |--------------------------------------------------------------------------
            */

            'provider' => $this->provider,

            'provider_reference' => $this->provider_reference,

            'provider_status' => $this->provider_status,

            'provider_response' => $this->provider_response,

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
            | Retry
            |--------------------------------------------------------------------------
            */

            'retry_count' => $this->retry_count,

            'last_retry_at' => optional(
                $this->last_retry_at
            )?->toDateTimeString(),

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

            'failed_at' => optional(
                $this->failed_at
            )?->toDateTimeString(),

            'cancelled_at' => optional(
                $this->cancelled_at
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