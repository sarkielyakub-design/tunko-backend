<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class KycCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return [

            'success' => true,

            'data' => KycResource::collection(
                $this->collection
            ),

            'pagination' => [

                'current_page' => $this->currentPage(),

                'last_page' => $this->lastPage(),

                'per_page' => $this->perPage(),

                'total' => $this->total(),

                'from' => $this->firstItem(),

                'to' => $this->lastItem(),

                'has_more_pages' => $this->hasMorePages(),

            ],

        ];
    }

    /**
     * Additional response data.
     */
    public function with(Request $request): array
    {
        return [

            'message' => 'KYC records retrieved successfully.',

        ];
    }
}