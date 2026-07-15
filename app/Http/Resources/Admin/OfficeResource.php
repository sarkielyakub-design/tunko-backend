<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfficeResource extends JsonResource
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

            'slug' => $this->slug,

            /*
            |--------------------------------------------------------------------------
            | Location
            |--------------------------------------------------------------------------
            */

            'country' => $this->country,

            'state' => $this->state,

            'city' => $this->city,

            'address' => $this->address,

            'full_address' => $this->full_address,

            /*
            |--------------------------------------------------------------------------
            | Contact
            |--------------------------------------------------------------------------
            */

            'email' => $this->email,

            'phone' => $this->phone,

            'whatsapp' => $this->whatsapp,

            /*
            |--------------------------------------------------------------------------
            | Coordinates
            |--------------------------------------------------------------------------
            */

            'latitude' => $this->latitude,

            'longitude' => $this->longitude,

            'google_maps_url' => $this->google_maps_url,

            /*
            |--------------------------------------------------------------------------
            | Business
            |--------------------------------------------------------------------------
            */

            'timezone' => $this->timezone,

            'currency' => $this->currency,

            'is_head_office' => (bool) $this->is_head_office,

            'is_active' => (bool) $this->is_active,

            'sort_order' => $this->sort_order,

            /*
            |--------------------------------------------------------------------------
            | SEO
            |--------------------------------------------------------------------------
            */

            'meta_title' => $this->meta_title,

            'meta_description' => $this->meta_description,

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