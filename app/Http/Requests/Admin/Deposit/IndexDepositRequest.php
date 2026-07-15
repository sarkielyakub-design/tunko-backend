<?php

namespace App\Http\Requests\Admin\Deposit;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexDepositRequest extends FormRequest
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

                    'completed',

                    'failed',

                    'cancelled',

                    'refunded',

                ]),

            ],

            /*
            |--------------------------------------------------------------------------
            | Gateway
            |--------------------------------------------------------------------------
            */

            'gateway' => [

                'nullable',

                'string',

                'max:100',

            ],

            /*
            |--------------------------------------------------------------------------
            | Payment Method
            |--------------------------------------------------------------------------
            */

            'payment_method' => [

                'nullable',

                Rule::in([

                    'bank_transfer',

                    'card',

                    'mobile_money',

                    'wallet',

                    'cash',

                ]),

            ],

            /*
            |--------------------------------------------------------------------------
            | Currency
            |--------------------------------------------------------------------------
            */

            'currency' => [

                'nullable',

                'string',

                'max:10',

            ],

            /*
            |--------------------------------------------------------------------------
            | User
            |--------------------------------------------------------------------------
            */

            'user_id' => [

                'nullable',

                'exists:users,id',

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