<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecipientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'name' => $this->name,

            'phone' => $this->phone,

            'email' => $this->email,

            'country' => $this->country,

            'currency' => $this->currency,

            'wallet_number' => $this->wallet_number,

            'bank_name' => $this->bank_name,

            'account_number' => $this->account_number,
        ];
    }
}