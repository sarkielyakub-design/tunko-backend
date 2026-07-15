<?php

namespace App\Http\Requests\Admin\Deposit;

use Illuminate\Foundation\Http\FormRequest;

class RejectDepositRequest extends FormRequest
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

                'in:payment_not_received,invalid_proof,duplicate_payment,fraud_suspected,amount_mismatch,gateway_error,expired_payment,manual_rejection,other',

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
            | Notify Customer
            |--------------------------------------------------------------------------
            */

            'notify_user' => [

                'nullable',

                'boolean',

            ],

            /*
            |--------------------------------------------------------------------------
            | Allow Resubmission
            |--------------------------------------------------------------------------
            */

            'allow_resubmission' => [

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

            'allow_resubmission' => $this->has('allow_resubmission')
                ? $this->boolean('allow_resubmission')
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