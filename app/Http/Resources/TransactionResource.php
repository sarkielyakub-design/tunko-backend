<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'type' => $this->type,
            'amount' => (float) $this->amount,
            'fee' => (float) $this->fee,
            'total' => (float) $this->total,
            'status' => $this->status,
            'description' => $this->description,
            'created_at' => $this->created_at,
        ];
    }
}