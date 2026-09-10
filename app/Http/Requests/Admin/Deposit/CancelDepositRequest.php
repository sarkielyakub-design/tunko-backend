<?php

namespace App\Http\Requests\Admin\Deposit;

use Illuminate\Foundation\Http\FormRequest;

class CancelDepositRequest extends FormRequest
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
            | Cancellation Reason
            |--------------------------------------------------------------------------
            */

            'reason' => [

                'required',

                'string',

                'min:10',

                'max:1000',

            ],

            /*
            |--------------------------------------------------------------------------
            | Cancellation Code
            |--------------------------------------------------------------------------
            */

            'cancel_code' => [

                'required',

                'in:user_request,duplicate_payment,gateway_timeout,gateway_unavailable,payment_expired,fraud_suspected,manual_cancel,technical_issue,other',

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
            | Refund Payment
            |--------------------------------------------------------------------------
            */

            'refund_payment' => [

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

            'refund_payment' => $this->has('refund_payment')
                ? $this->boolean('refund_payment')
                : false,

        ]);
    }

    /**
     * Custom Messages
     */
    public function messages(): array
    {
        return [

            'reason.required' => 'Cancellation reason is required.',

            'reason.min' => 'Please provide a detailed cancellation reason.',

            'cancel_code.required' => 'Cancellation code is required.',

            'cancel_code.in' => 'Invalid cancellation code selected.',

        ];
    }
}