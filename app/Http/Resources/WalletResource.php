<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'wallet_number' => $this->wallet_number,
            'balance' => (float) $this->balance,
            'currency' => $this->currency,
            'is_active' => $this->is_active,
        ];
    }
}