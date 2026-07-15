<?php

namespace App\Http\Requests\Admin\Kyc;

use Illuminate\Foundation\Http\FormRequest;

class ApproveKycRequest extends FormRequest
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
            | KYC Level
            |--------------------------------------------------------------------------
            */

            'kyc_level' => [

                'required',

                'integer',

                'min:1',

                'max:4',

            ],

            /*
            |--------------------------------------------------------------------------
            | Approval Note
            |--------------------------------------------------------------------------
            */

            'note' => [

                'nullable',

                'string',

                'max:1000',

            ],

            /*
            |--------------------------------------------------------------------------
            | Verification Provider
            |--------------------------------------------------------------------------
            */

            'verification_provider' => [

                'nullable',

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
            | Notify User
            |--------------------------------------------------------------------------
            */

            'notify_user' => [

                'nullable',

                'boolean',

            ],

            /*
            |--------------------------------------------------------------------------
            | Increase Limits
            |--------------------------------------------------------------------------
            */

            'update_transaction_limits' => [

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

            'update_transaction_limits' => $this->has('update_transaction_limits')
                ? $this->boolean('update_transaction_limits')
                : true,

        ]);
    }

    /**
     * Custom Messages
     */
    public function messages(): array
    {
        return [

            'kyc_level.required' => 'KYC level is required.',

            'kyc_level.min' => 'Invalid KYC level.',

            'kyc_level.max' => 'Invalid KYC level.',

        ];
    }
}