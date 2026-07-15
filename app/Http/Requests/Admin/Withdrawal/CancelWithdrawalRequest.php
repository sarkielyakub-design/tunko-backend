<?php

namespace App\Http\Requests\Admin\Withdrawal;

use Illuminate\Foundation\Http\FormRequest;

class CancelWithdrawalRequest extends FormRequest
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

                'in:user_request,duplicate_request,technical_issue,provider_unavailable,compliance_issue,fraud_suspected,manual_cancel,timeout,other',

            ],

            /*
            |--------------------------------------------------------------------------
            | Refund Wallet
            |--------------------------------------------------------------------------
            */

            'refund_wallet' => [

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

            'refund_wallet' => $this->has('refund_wallet')
                ? $this->boolean('refund_wallet')
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

            'reason.required' => 'Cancellation reason is required.',

            'reason.min' => 'Please provide a detailed cancellation reason.',

            'cancel_code.required' => 'Cancellation code is required.',

            'cancel_code.in' => 'Invalid cancellation code selected.',

        ];
    }
}