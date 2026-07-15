<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Wallet
            |--------------------------------------------------------------------------
            */

            'id' => $this->id,

            'wallet_number' => $this->wallet_number,

            'currency' => $this->currency,

            /*
            |--------------------------------------------------------------------------
            | Owner
            |--------------------------------------------------------------------------
            */

            'user' => [

                'id' => $this->user?->id,

                'name' => $this->user?->full_name,

                'first_name' => $this->user?->first_name,

                'last_name' => $this->user?->last_name,

                'email' => $this->user?->email,

                'phone' => $this->user?->phone,

                'country' => $this->user?->country,

                'is_verified' => (bool) $this->user?->is_verified,

                'is_active' => (bool) $this->user?->is_active,

            ],

            /*
            |--------------------------------------------------------------------------
            | Balance
            |--------------------------------------------------------------------------
            */

            'balance' => (float) $this->balance,

            'available_balance' => (float) (
                $this->available_balance ?? $this->balance
            ),

            'locked_balance' => (float) (
                $this->locked_balance ?? 0
            ),

            'pending_balance' => (float) (
                $this->pending_balance ?? 0
            ),

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            'is_active' => (bool) $this->is_active,

            'is_frozen' => (bool) (
                $this->is_frozen ?? false
            ),

            'freeze_reason' => $this->freeze_reason,

            /*
            |--------------------------------------------------------------------------
            | Statistics
            |--------------------------------------------------------------------------
            */

            'transactions_count' => $this->whenCounted(
                'walletTransactions'
            ),

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