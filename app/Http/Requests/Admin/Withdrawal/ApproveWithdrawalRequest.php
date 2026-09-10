<?php

namespace App\Http\Requests\Admin\Withdrawal;

use Illuminate\Foundation\Http\FormRequest;

class ApproveWithdrawalRequest extends FormRequest
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
            | Debit Wallet
            |--------------------------------------------------------------------------
            */

            'debit_wallet' => [

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

            'debit_wallet' => $this->has('debit_wallet')
                ? $this->boolean('debit_wallet')
                : false,

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

            'provider.required' => 'Payment provider is required.',

            'provider_status.required' => 'Provider status is required.',

        ];
    }
}