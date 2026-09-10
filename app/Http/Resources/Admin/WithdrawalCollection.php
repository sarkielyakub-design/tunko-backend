<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class WithdrawalCollection extends ResourceCollection
{
    /**
     * Transform the collection.
     */
    public function toArray(Request $request): array
    {
        return [

            'success' => true,

            'data' => WithdrawalResource::collection(
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

                'total_withdrawals' => $this->total(),

                'current_page' => $this->currentPage(),

            ],

        ];
    }
}