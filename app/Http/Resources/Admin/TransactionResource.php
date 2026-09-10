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

            'first_name' => $this->user?->first_name,

            'last_name' => $this->user?->last_name,

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

        'meta' => $this->meta ?? [],

        /*
        |--------------------------------------------------------------------------
        | Admin
        |--------------------------------------------------------------------------
        */

        'admin' => [

            'id' => data_get($this->meta, 'admin_id'),

            'name' => data_get($this->meta, 'admin_name'),

            'updated_by' => data_get($this->meta, 'updated_by'),

        ],

        /*
        |--------------------------------------------------------------------------
        | Refund
        |--------------------------------------------------------------------------
        */

        'refund' => [

            'original_transaction' => data_get(
                $this->meta,
                'original_transaction'
            ),

            'refunded_at' => data_get(
                $this->meta,
                'refunded_at'
            ),

        ],

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

        /*
        |--------------------------------------------------------------------------
        | UI Helpers
        |--------------------------------------------------------------------------
        */

        'is_success' => $this->status === 'success',

        'is_pending' => $this->status === 'pending',

        'is_failed' => $this->status === 'failed',

        'is_refunded' => $this->status === 'refunded',

        'can_refund' => $this->status === 'success'
            && $this->type !== 'refund',

    ];
}
}