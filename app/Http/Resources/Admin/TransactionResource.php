<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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
            | Transaction
            |--------------------------------------------------------------------------
            */

            'type' => $this->type,

            'status' => $this->status,

            'payment_gateway' => $this->payment_gateway,

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
            | Description
            |--------------------------------------------------------------------------
            */

            'description' => $this->description,

            /*
            |--------------------------------------------------------------------------
            | Metadata
            |--------------------------------------------------------------------------
            */

            'meta' => $this->meta,

            /*
            |--------------------------------------------------------------------------
            | Dates
            |--------------------------------------------------------------------------
            */

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