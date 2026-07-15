<?php

namespace App\Http\Requests\Admin\Transfer;

use Illuminate\Foundation\Http\FormRequest;

class RejectTransferRequest extends FormRequest
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
            | Reject Reason
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
            | Reject Code
            |--------------------------------------------------------------------------
            */

            'reject_code' => [

                'required',

                'in:kyc_failed,aml_failed,sanction_match,insufficient_information,duplicate_transfer,recipient_invalid,bank_rejected,compliance_failed,fraud_suspected,provider_error,other',

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

            'reason.required' => 'Rejection reason is required.',

            'reason.min' => 'Please provide a detailed rejection reason.',

            'reject_code.required' => 'Reject code is required.',

            'reject_code.in' => 'Invalid rejection code selected.',

        ];
    }
}