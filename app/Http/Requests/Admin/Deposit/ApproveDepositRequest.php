<?php

namespace App\Http\Requests\Admin\Deposit;

use Illuminate\Foundation\Http\FormRequest;

class ApproveDepositRequest extends FormRequest
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
            | Gateway Reference
            |--------------------------------------------------------------------------
            */

            'gateway_reference' => [

                'required',

                'string',

                'max:255',

            ],

            /*
            |--------------------------------------------------------------------------
            | Provider Status
            |--------------------------------------------------------------------------
            */

            'provider_status' => [

                'required',

                'string',

                'max:100',

            ],

            /*
            |--------------------------------------------------------------------------
            | Provider Response
            |--------------------------------------------------------------------------
            */

            'provider_response' => [

                'nullable',

                'string',

                'max:5000',

            ],

            /*
            |--------------------------------------------------------------------------
            | Credit Wallet
            |--------------------------------------------------------------------------
            */

            'credit_wallet' => [

                'nullable',

                'boolean',

            ],

            /*
            |--------------------------------------------------------------------------
            | Notify Customer
            |--------------------------------------------------------------------------
            */

            'notify_user' => [

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

        ];
    }

    /**
     * Prepare Validation
     */
    protected function prepareForValidation(): void
    {
        $this->merge([

            'credit_wallet' => $this->has('credit_wallet')
                ? $this->boolean('credit_wallet')
                : true,

            'notify_user' => $this->has('notify_user')
                ? $this->boolean('notify_user')
                : true,

        ]);
    }

    /**
     * Custom Messages
     */
    public function messages(): array
    {
        return [

            'gateway_reference.required' => 'Gateway reference is required.',

            'provider_status.required' => 'Provider status is required.',

        ];
    }
}