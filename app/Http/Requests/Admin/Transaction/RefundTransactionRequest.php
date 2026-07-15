<?php

namespace App\Http\Requests\Admin\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class RefundTransactionRequest extends FormRequest
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
            | Refund Amount
            |--------------------------------------------------------------------------
            */

            'amount' => [

                'required',

                'numeric',

                'min:0.01',

            ],

            /*
            |--------------------------------------------------------------------------
            | Refund Reason
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
            | Refund Type
            |--------------------------------------------------------------------------
            */

            'refund_type' => [

                'required',

                'in:full,partial',

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

            'amount.required' => 'Refund amount is required.',

            'amount.numeric' => 'Refund amount must be numeric.',

            'reason.required' => 'Refund reason is required.',

            'refund_type.required' => 'Refund type is required.',

            'refund_type.in' => 'Refund type must be full or partial.',

        ];
    }
}