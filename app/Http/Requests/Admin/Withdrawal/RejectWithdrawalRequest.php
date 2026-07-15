<?php

namespace App\Http\Requests\Admin\Withdrawal;

use Illuminate\Foundation\Http\FormRequest;

class RejectWithdrawalRequest extends FormRequest
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

                'in:insufficient_balance,kyc_required,kyc_failed,aml_failed,fraud_suspected,invalid_bank_account,bank_rejected,provider_error,compliance_failed,manual_rejection,other',

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

            'reject_code.in' => 'Invalid reject code selected.',

        ];
    }
}