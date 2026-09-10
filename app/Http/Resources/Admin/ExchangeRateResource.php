<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExchangeRateResource extends JsonResource
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

            /*
            |--------------------------------------------------------------------------
            | Currency Pair
            |--------------------------------------------------------------------------
            */

            'base_currency' => $this->base_currency,

            'target_currency' => $this->target_currency,

            /*
            |--------------------------------------------------------------------------
            | Rates
            |--------------------------------------------------------------------------
            */

            'rate' => (float) $this->rate,

            'markup' => (float) $this->markup,

            'final_rate' => (float) $this->final_rate,

            /*
            |--------------------------------------------------------------------------
            | Source
            |--------------------------------------------------------------------------
            */

            'source' => $this->source,

            'is_manual' => (bool) $this->is_manual,

            'is_active' => (bool) $this->is_active,

            /*
            |--------------------------------------------------------------------------
            | Synchronization
            |--------------------------------------------------------------------------
            */

            'last_synced_at' => optional(
                $this->last_synced_at
            )?->toDateTimeString(),

            /*
            |--------------------------------------------------------------------------
            | Audit
            |--------------------------------------------------------------------------
            */

            'updated_by' => $this->updated_by,

            'note' => $this->note,

            'metadata' => $this->metadata,

            /*
            |--------------------------------------------------------------------------
            | Dates
            |--------------------------------------------------------------------------
            */

            'created_at' => optional(
                $this->created_at
            )?->toDateTimeString(),

            'updated_at' => optional(
                $this->updated_at
            )?->toDateTimeString(),

        ];
    }
}