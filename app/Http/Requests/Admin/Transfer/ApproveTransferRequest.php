<?php

namespace App\Http\Requests\Admin\Transfer;

use Illuminate\Foundation\Http\FormRequest;

class ApproveTransferRequest extends FormRequest
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

                'string',

                'max:100',

            ],

            /*
            |--------------------------------------------------------------------------
            | Exchange Rate
            |--------------------------------------------------------------------------
            */

            'exchange_rate' => [

                'required',

                'numeric',

                'gt:0',

            ],

            /*
            |--------------------------------------------------------------------------
            | Recipient Amount
            |--------------------------------------------------------------------------
            */

            'recipient_amount' => [

                'required',

                'numeric',

                'gt:0',

            ],

            /*
            |--------------------------------------------------------------------------
            | Provider Reference
            |--------------------------------------------------------------------------
            */

            'provider_reference' => [

                'nullable',

                'string',

                'max:255',

            ],

            /*
            |--------------------------------------------------------------------------
            | Compliance Passed
            |--------------------------------------------------------------------------
            */

            'compliance_passed' => [

                'nullable',

                'boolean',

            ],

            /*
            |--------------------------------------------------------------------------
            | Admin Note
            |--------------------------------------------------------------------------
            */

            'note' => [

                'nullable',

                'string',

                'max:1000',

            ],

            /*
            |--------------------------------------------------------------------------
            | Notify User
            |--------------------------------------------------------------------------
            */

            'notify_user' => [

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

            'notify_user' => $this->has('notify_user')
                ? $this->boolean('notify_user')
                : true,

            'compliance_passed' => $this->has('compliance_passed')
                ? $this->boolean('compliance_passed')
                : true,

        ]);
    }

    /**
     * Messages
     */
    public function messages(): array
    {
        return [

            'provider.required' => 'Transfer provider is required.',

            'exchange_rate.required' => 'Exchange rate is required.',

            'exchange_rate.gt' => 'Exchange rate must be greater than zero.',

            'recipient_amount.required' => 'Recipient amount is required.',

            'recipient_amount.gt' => 'Recipient amount must be greater than zero.',

        ];
    }
}