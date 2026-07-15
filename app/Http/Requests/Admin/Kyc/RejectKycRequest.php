<?php

namespace App\Http\Requests\Admin\Kyc;

use Illuminate\Foundation\Http\FormRequest;

class RejectKycRequest extends FormRequest
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
            | Rejection Reason
            |--------------------------------------------------------------------------
            */

            'reason' => [

                'required',

                'string',

                'min:10',

                'max:500',

            ],

            /*
            |--------------------------------------------------------------------------
            | Reject Code
            |--------------------------------------------------------------------------
            */

            'reject_code' => [

                'required',

                'in:document_blurry,document_expired,document_invalid,selfie_mismatch,duplicate_account,address_invalid,information_mismatch,fraud_suspected,other',

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

            /*
            |--------------------------------------------------------------------------
            | Notify Customer
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