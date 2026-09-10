<?php

namespace App\Http\Requests\Admin\ExchangeRate;

use Illuminate\Foundation\Http\FormRequest;

class StoreExchangeRateRequest extends FormRequest
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
            | Base Currency
            |--------------------------------------------------------------------------
            */

            'base_currency' => [

                'required',

                'string',

                'size:3',

            ],

            /*
            |--------------------------------------------------------------------------
            | Target Currency
            |--------------------------------------------------------------------------
            */

            'target_currency' => [

                'required',

                'string',

                'size:3',

                'different:base_currency',

            ],

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
            | Markup (%)
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
            | Manual Rate
            |--------------------------------------------------------------------------
            */

            'is_manual' => [

                'nullable',

                'boolean',

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
            | Notes
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

            'is_manual' => $this->has('is_manual')
                ? $this->boolean('is_manual')
                : true,

            'is_active' => $this->has('is_active')
                ? $this->boolean('is_active')
                : true,

        ]);
    }

    /**
     * Custom Messages
     */
    public function messages(): array
    {
        return [

            'base_currency.required' => 'Base currency is required.',

            'target_currency.required' => 'Target currency is required.',

            'target_currency.different' => 'Base and target currencies cannot be the same.',

            'rate.required' => 'Exchange rate is required.',

            'rate.gt' => 'Exchange rate must be greater than zero.',

        ];
    }
}