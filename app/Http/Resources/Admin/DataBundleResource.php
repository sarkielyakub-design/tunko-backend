<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DataBundleResource extends JsonResource
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

            'name' => $this->name,

            'size' => $this->size,

            'provider_bundle_id' => $this->provider_bundle_id,

            /*
            |--------------------------------------------------------------------------
            | Country
            |--------------------------------------------------------------------------
            */

            'country' => [

                'id' => $this->country?->id,

                'name' => $this->country?->name,

                'iso2' => $this->country?->iso2,

                'currency' => $this->country?->currency,

                'flag' => $this->country?->flag,

            ],

            /*
            |--------------------------------------------------------------------------
            | Network
            |--------------------------------------------------------------------------
            */

            'network' => [

                'id' => $this->network?->id,

                'name' => $this->network?->name,

                'code' => $this->network?->code,

                'logo' => $this->network?->logo,

                'color' => $this->network?->color,

            ],

            /*
            |--------------------------------------------------------------------------
            | Provider
            |--------------------------------------------------------------------------
            */

            'provider' => $this->provider,

            /*
            |--------------------------------------------------------------------------
            | Validity
            |--------------------------------------------------------------------------
            */

            'validity_days' => $this->validity_days,

            /*
            |--------------------------------------------------------------------------
            | Pricing
            |--------------------------------------------------------------------------
            */

            'provider_price' => (float) $this->provider_price,

            'selling_price' => (float) $this->selling_price,

            'commission' => (float) $this->commission,

            /*
            |--------------------------------------------------------------------------
            | Profit
            |--------------------------------------------------------------------------
            */

            'profit' => round(

                $this->selling_price -

                $this->provider_price,

                2

            ),

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            'sort_order' => $this->sort_order,

            'is_active' => (bool) $this->is_active,

            /*
            |--------------------------------------------------------------------------
            | Dates
            |--------------------------------------------------------------------------
            */

            'created_at' => optional(
                $this->created_at
            )->toDateTimeString(),

            'updated_at' => optional(
                $this->updated_at
            )->toDateTimeString(),

        ];
    }
}