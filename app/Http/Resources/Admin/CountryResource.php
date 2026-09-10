<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Basic Information
            |--------------------------------------------------------------------------
            */

            'id' => $this->id,

            'name' => $this->name,

            'official_name' => $this->official_name,

            'iso2' => $this->iso2,

            'iso3' => $this->iso3,

            'continent' => $this->continent,

            /*
            |--------------------------------------------------------------------------
            | Currency
            |--------------------------------------------------------------------------
            */

            'currency' => $this->currency,

            'currency_name' => $this->currency_name,

            'currency_symbol' => $this->currency_symbol,

            'exchange_rate' => (float) $this->exchange_rate,

            /*
            |--------------------------------------------------------------------------
            | Contact
            |--------------------------------------------------------------------------
            */

            'phone_code' => $this->phone_code,

            'timezone' => $this->timezone,

            'flag' => $this->flag,

            /*
            |--------------------------------------------------------------------------
            | Services
            |--------------------------------------------------------------------------
            */

            'wallet_enabled' => (bool) $this->wallet_enabled,

            'transfer_enabled' => (bool) $this->transfer_enabled,

            'airtime_enabled' => (bool) $this->airtime_enabled,

            'data_enabled' => (bool) $this->data_enabled,

            'kyc_required' => (bool) $this->kyc_required,

            /*
            |--------------------------------------------------------------------------
            | Limits
            |--------------------------------------------------------------------------
            */

            'minimum_transfer' => $this->minimum_transfer,

            'maximum_transfer' => $this->maximum_transfer,

            'minimum_wallet_funding' => $this->minimum_wallet_funding,

            'maximum_wallet_funding' => $this->maximum_wallet_funding,

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

            'created_at' => optional($this->created_at)
                ->toDateTimeString(),

            'updated_at' => optional($this->updated_at)
                ->toDateTimeString(),

        ];
    }
}