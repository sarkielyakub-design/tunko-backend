<?php

namespace App\Http\Requests\Admin\Transfer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexTransferRequest extends FormRequest
{
    /**
     * Authorize
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation Rules
     */
    public function rules(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Search
            |--------------------------------------------------------------------------
            */

            'search' => [

                'nullable',

                'string',

                'max:150',

            ],

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            'status' => [

                'nullable',

                Rule::in([

                    'pending',

                    'processing',

                    'waiting_compliance',

                    'approved',

                    'sent',

                    'completed',

                    'failed',

                    'cancelled',

                    'refunded',

                ]),

            ],

            /*
            |--------------------------------------------------------------------------
            | Country
            |--------------------------------------------------------------------------
            */

            'source_country' => [

                'nullable',

                'string',

                'max:100',

            ],

            'destination_country' => [

                'nullable',

                'string',

                'max:100',

            ],

            /*
            |--------------------------------------------------------------------------
            | Provider
            |--------------------------------------------------------------------------
            */

            'provider' => [

                'nullable',

                'string',

                'max:100',

            ],

            /*
            |--------------------------------------------------------------------------
            | Currency
            |--------------------------------------------------------------------------
            */

            'sender_currency' => [

                'nullable',

                'string',

                'max:10',

            ],

            'recipient_currency' => [

                'nullable',

                'string',

                'max:10',

            ],

            /*
            |--------------------------------------------------------------------------
            | Amount
            |--------------------------------------------------------------------------
            */

            'min_amount' => [

                'nullable',

                'numeric',

                'min:0',

            ],

            'max_amount' => [

                'nullable',

                'numeric',

                'gte:min_amount',

            ],

            /*
            |--------------------------------------------------------------------------
            | Date
            |--------------------------------------------------------------------------
            */

            'from_date' => [

                'nullable',

                'date',

            ],

            'to_date' => [

                'nullable',

                'date',

                'after_or_equal:from_date',

            ],

            /*
            |--------------------------------------------------------------------------
            | Sorting
            |--------------------------------------------------------------------------
            */

            'sort' => [

                'nullable',

                Rule::in([

                    'created_at',

                    'amount',

                    'recipient_amount',

                    'status',

                ]),

            ],

            'direction' => [

                'nullable',

                Rule::in([

                    'asc',

                    'desc',

                ]),

            ],

            /*
            |--------------------------------------------------------------------------
            | Pagination
            |--------------------------------------------------------------------------
            */

            'per_page' => [

                'nullable',

                'integer',

                'min:10',

                'max:100',

            ],

        ];
    }

    /**
     * Prepare Validation
     */
    protected function prepareForValidation(): void
    {
        $this->merge([

            'sort' => $this->sort ?? 'created_at',

            'direction' => $this->direction ?? 'desc',

            'per_page' => $this->per_page ?? 20,

        ]);
    }
}