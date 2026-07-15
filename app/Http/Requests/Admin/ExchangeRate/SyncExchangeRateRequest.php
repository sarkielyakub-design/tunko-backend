<?php

namespace App\Http\Requests\Admin\ExchangeRate;

use Illuminate\Foundation\Http\FormRequest;

class SyncExchangeRateRequest extends FormRequest
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
            | Provider
            |--------------------------------------------------------------------------
            */

            'provider' => [

                'required',

                'in:openexchangerates,fixer,exchangerate_api,currencylayer,manual',

            ],

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
            | Target Currencies
            |--------------------------------------------------------------------------
            */

            'currencies' => [

                'required',

                'array',

                'min:1',

            ],

            'currencies.*' => [

                'string',

                'size:3',

            ],

            /*
            |--------------------------------------------------------------------------
            | Overwrite Existing
            |--------------------------------------------------------------------------
            */

            'overwrite' => [

                'nullable',

                'boolean',

            ],

            /*
            |--------------------------------------------------------------------------
            | Apply Markup
            |--------------------------------------------------------------------------
            */

            'apply_markup' => [

                'nullable',

                'boolean',

            ],

            /*
            |--------------------------------------------------------------------------
            | Notify Admin
            |--------------------------------------------------------------------------
            */

            'notify_admin' => [

                'nullable',

                'boolean',

            ],

        ];
    }

    /**
     * Prepare Validation
     */
    protected function prepareForValidation(): void
    {
        $this->merge([

            'overwrite' => $this->has('overwrite')
                ? $this->boolean('overwrite')
                : true,

            'apply_markup' => $this->has('apply_markup')
                ? $this->boolean('apply_markup')
                : true,

            'notify_admin' => $this->has('notify_admin')
                ? $this->boolean('notify_admin')
                : false,

        ]);
    }

    /**
     * Messages
     */
    public function messages(): array
    {
        return [

            'provider.required' => 'Exchange rate provider is required.',

            'provider.in' => 'Unsupported exchange rate provider.',

            'base_currency.required' => 'Base currency is required.',

            'currencies.required' => 'Select at least one target currency.',

            'currencies.array' => 'Currencies must be an array.',

        ];
    }
}