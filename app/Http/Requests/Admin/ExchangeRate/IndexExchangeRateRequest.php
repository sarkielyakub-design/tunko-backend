<?php

namespace App\Http\Requests\Admin\ExchangeRate;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexExchangeRateRequest extends FormRequest
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

                'max:100',

            ],

            /*
            |--------------------------------------------------------------------------
            | Base Currency
            |--------------------------------------------------------------------------
            */

            'base_currency' => [

                'nullable',

                'string',

                'size:3',

            ],

            /*
            |--------------------------------------------------------------------------
            | Target Currency
            |--------------------------------------------------------------------------
            */

            'target_currency' => [

                'nullable',

                'string',

                'size:3',

            ],

            /*
            |--------------------------------------------------------------------------
            | Source
            |--------------------------------------------------------------------------
            */

            'source' => [

                'nullable',

                Rule::in([

                    'manual',

                    'api',

                    'system',

                ]),

            ],

            /*
            |--------------------------------------------------------------------------
            | Active
            |--------------------------------------------------------------------------
            */

            'is_active' => [

                'nullable',

                'boolean',

            ],

            /*
            |--------------------------------------------------------------------------
            | Manual
            |--------------------------------------------------------------------------
            */

            'is_manual' => [

                'nullable',

                'boolean',

            ],

            /*
            |--------------------------------------------------------------------------
            | Sort
            |--------------------------------------------------------------------------
            */

            'sort' => [

                'nullable',

                Rule::in([

                    'created_at',

                    'updated_at',

                    'rate',

                    'final_rate',

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

            'sort' => $this->sort ?? 'updated_at',

            'direction' => $this->direction ?? 'desc',

            'per_page' => $this->per_page ?? 20,

        ]);
    }
}