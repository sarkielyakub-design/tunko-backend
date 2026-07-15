<?php

namespace App\Http\Requests\Admin\Transfer;

use Illuminate\Foundation\Http\FormRequest;

class CancelTransferRequest extends FormRequest
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
            | Cancel Reason
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
            | Cancel Code
            |--------------------------------------------------------------------------
            */

            'cancel_code' => [

                'required',

                'in:user_request,duplicate_transfer,provider_timeout,provider_unavailable,incorrect_beneficiary,compliance_issue,technical_issue,fraud_suspected,other',

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
     * Messages
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