<?php

namespace App\Http\Requests\Admin\Wallet;

use Illuminate\Foundation\Http\FormRequest;

class CreditWalletRequest extends FormRequest
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
            | Amount
            |--------------------------------------------------------------------------
            */

            'amount' => [

                'required',

                'numeric',

                'min:1',

                'max:1000000000',

            ],

            /*
            |--------------------------------------------------------------------------
            | Reason
            |--------------------------------------------------------------------------
            */

            'reason' => [

                'required',

                'string',

                'min:5',

                'max:500',

            ],

            /*
            |--------------------------------------------------------------------------
            | Reference
            |--------------------------------------------------------------------------
            */

            'reference' => [

                'nullable',

                'string',

                'max:100',

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
            | Notification
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

        ]);
    }

    /**
     * Messages
     */
    public function messages(): array
    {
        return [

            'amount.required' => 'Credit amount is required.',

            'amount.numeric' => 'Amount must be numeric.',

            'amount.min' => 'Amount must be greater than zero.',

            'reason.required' => 'Reason is required.',

        ];
    }
}