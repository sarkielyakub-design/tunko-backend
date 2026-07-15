<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class WalletCollection extends ResourceCollection
{
    /**
     * Transform the resource collection.
     */
    public function toArray(Request $request): array
    {
        return [

            'success' => true,

            'data' => WalletResource::collection(
                $this->collection
            ),

            'pagination' => [

                'current_page' => $this->currentPage(),

                'last_page' => $this->lastPage(),

                'per_page' => $this->perPage(),

                'total' => $this->total(),

                'from' => $this->firstItem(),

                'to' => $this->lastItem(),

            ],

            /*
            |--------------------------------------------------------------------------
            | Summary
            |--------------------------------------------------------------------------
            */

            'summary' => [

                'wallets' => $this->total(),

            ],

        ];
    }
}