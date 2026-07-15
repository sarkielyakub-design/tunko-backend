<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransferResource extends JsonResource
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

            'reference' => $this->reference,

            /*
            |--------------------------------------------------------------------------
            | Sender
            |--------------------------------------------------------------------------
            */

            'sender' => [

                'id' => $this->user?->id,

                'name' => $this->user?->full_name,

                'email' => $this->user?->email,

                'phone' => $this->user?->phone,

            ],

            /*
            |--------------------------------------------------------------------------
            | Recipient
            |--------------------------------------------------------------------------
            */

            'recipient' => [

                'name' => $this->recipient_name,

                'phone' => $this->recipient_phone,

                'email' => $this->recipient_email,

                'bank_name' => $this->bank_name,

                'account_number' => $this->account_number,

                'country' => $this->destination_country,

            ],

            /*
            |--------------------------------------------------------------------------
            | Amount
            |--------------------------------------------------------------------------
            */

            'amount' => (float) $this->amount,

            'fee' => (float) $this->fee,

            'total' => (float) $this->total,

            'exchange_rate' => (float) $this->exchange_rate,

            'recipient_amount' => (float) $this->recipient_amount,

            /*
            |--------------------------------------------------------------------------
            | Currency
            |--------------------------------------------------------------------------
            */

            'sender_currency' => $this->sender_currency,

            'recipient_currency' => $this->recipient_currency,

            /*
            |--------------------------------------------------------------------------
            | Provider
            |--------------------------------------------------------------------------
            */

            'provider' => $this->provider,

            'provider_reference' => $this->provider_reference,

            'provider_status' => $this->provider_status,

            /*
            |--------------------------------------------------------------------------
            | Compliance
            |--------------------------------------------------------------------------
            */

            'compliance_status' => $this->compliance_status,

            'risk_score' => $this->risk_score,

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            'status' => $this->status,

            /*
            |--------------------------------------------------------------------------
            | Purpose
            |--------------------------------------------------------------------------
            */

            'purpose' => $this->purpose,

            'source_of_funds' => $this->source_of_funds,

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