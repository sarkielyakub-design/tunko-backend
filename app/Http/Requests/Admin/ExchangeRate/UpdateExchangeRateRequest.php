<?php

namespace App\Http\Requests\Admin\ExchangeRate;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExchangeRateRequest extends FormRequest
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
            | Exchange Rate
            |--------------------------------------------------------------------------
            */

            'rate' => [

                'required',

                'numeric',

                'gt:0',

            ],

            /*
            |--------------------------------------------------------------------------
            | Markup
            |--------------------------------------------------------------------------
            */

            'markup' => [

                'nullable',

                'numeric',

                'min:0',

            ],

            /*
            |--------------------------------------------------------------------------
            | Source
            |--------------------------------------------------------------------------
            */

            'source' => [

                'required',

                'in:manual,api',

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
            | Manual Rate
            |--------------------------------------------------------------------------
            */

            'is_manual' => [

                'nullable',

                'boolean',

            ],

            /*
            |--------------------------------------------------------------------------
            | Note
            |--------------------------------------------------------------------------
            */

            'note' => [

                'nullable',

                'string',

                'max:1000',

            ],

        ];
    }

    /**
     * Prepare Validation
     */
    protected function prepareForValidation(): void
    {
        $this->merge([

            'is_active' => $this->has('is_active')
                ? $this->boolean('is_active')
                : true,

            'is_manual' => $this->has('is_manual')
                ? $this->boolean('is_manual')
                : true,

        ]);
    }

    /**
     * Custom Messages
     */
    public function messages(): array
    {
        return [

            'rate.required' => 'Exchange rate is required.',

            'rate.gt' => 'Exchange rate must be greater than zero.',

            'source.required' => 'Rate source is required.',

            'source.in' => 'Invalid rate source selected.',

        ];
    }
}