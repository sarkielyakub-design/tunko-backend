<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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

            'first_name' => $this->first_name,

            'last_name' => $this->last_name,

            'full_name' => $this->full_name,

            'username' => $this->username,

            'email' => $this->email,

            'phone' => $this->phone,

            'country' => $this->country,

            /*
            |--------------------------------------------------------------------------
            | Account Status
            |--------------------------------------------------------------------------
            */

            'is_verified' => (bool) $this->is_verified,

            'is_active' => (bool) $this->is_active,

            /*
            |--------------------------------------------------------------------------
            | Wallet
            |--------------------------------------------------------------------------
            */

            'wallet' => [

                'wallet_number' => optional(
                    $this->wallet
                )->wallet_number,

                'balance' => optional(
                    $this->wallet
                )->balance,

                'currency' => optional(
                    $this->wallet
                )->currency,

                'is_active' => optional(
                    $this->wallet
                )->is_active,

            ],

            /*
            |--------------------------------------------------------------------------
            | KYC
            |--------------------------------------------------------------------------
            */

            'kyc' => $this->kyc ? [

                'status' => $this->kyc->status,

                'document_type' => $this->kyc->document_type,

                'submitted_at' => $this->kyc->created_at,

            ] : null,

            /*
            |--------------------------------------------------------------------------
            | Statistics
            |--------------------------------------------------------------------------
            */

            'statistics' => [

                'transactions' => $this->whenLoaded(
                    'transactions',
                    fn () => $this->transactions->count(),
                    0
                ),

                'beneficiaries' => $this->whenLoaded(
                    'beneficiaries',
                    fn () => $this->beneficiaries->count(),
                    0
                ),

                'cards' => $this->whenLoaded(
                    'cards',
                    fn () => $this->cards->count(),
                    0
                ),

            ],

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