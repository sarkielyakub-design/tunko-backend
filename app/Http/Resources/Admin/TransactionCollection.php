<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TransactionCollection extends ResourceCollection
{
    /**
     * Transform collection.
     */
    public function toArray(Request $request): array
    {
        return [

            'success' => true,

            'data' => TransactionResource::collection(
                $this->collection
            ),

            /*
            |--------------------------------------------------------------------------
            | Pagination
            |--------------------------------------------------------------------------
            */

            'pagination' => [

                'current_page' => $this->currentPage(),

                'last_page' => $this->lastPage(),

                'per_page' => $this->perPage(),

                'total' => $this->total(),

                'from' => $this->firstItem(),

                'to' => $this->lastItem(),

                'has_more_pages' => $this->hasMorePages(),

            ],

            /*
            |--------------------------------------------------------------------------
            | Navigation
            |--------------------------------------------------------------------------
            */

            'links' => [

                'first' => $this->url(1),

                'last' => $this->url($this->lastPage()),

                'previous' => $this->previousPageUrl(),

                'next' => $this->nextPageUrl(),

            ],

            /*
            |--------------------------------------------------------------------------
            | Filters
            |--------------------------------------------------------------------------
            */

            'filters' => [

                'search' => $request->search,

                'status' => $request->status,

                'type' => $request->type,

                'payment_gateway' => $request->payment_gateway,

                'currency' => $request->currency,

                'from_date' => $request->from_date,

                'to_date' => $request->to_date,

                'sort' => $request->sort,

                'direction' => $request->direction,

            ],

        ];
    }
}