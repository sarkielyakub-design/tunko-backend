<?php

namespace App\Http\Resources\Office;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfficeStaffResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'is_active' => (bool) $this->is_active,
            'office' => $this->whenLoaded('office', fn () => [
                'id' => $this->office->id,
                'name' => $this->office->name,
                'country' => $this->office->country,
                'state' => $this->office->state,
                'city' => $this->office->city,
                'address' => $this->office->address,
                'phone' => $this->office->phone,
                'currency' => $this->office->currency,
                'timezone' => $this->office->timezone,
            ]),
            'last_login_at' => optional($this->last_login_at)->toDateTimeString(),
        ];
    }
}
