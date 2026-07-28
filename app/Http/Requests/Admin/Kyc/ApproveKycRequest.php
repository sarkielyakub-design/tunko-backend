<?php

namespace App\Http\Requests\Admin\Kyc;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
                'bail',
                'required',
                'integer',
                Rule::in([
                    1,
                    2,
                    3,
                    4,
                ]),
            ],

            /*
            |--------------------------------------------------------------------------
            | Approval Note
            |--------------------------------------------------------------------------
            */

            'note' => [
                'bail',
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
                'bail',
                'nullable',
                Rule::in([
                    'manual',
                    'sumsub',
                    'smile_identity',
                    'veriff',
                    'onfido',
                ]),
            ],

            /*
            |--------------------------------------------------------------------------
            | Provider Reference
            |--------------------------------------------------------------------------
            */

            'provider_reference' => [
                'bail',
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
                'bail',
                'nullable',
                'boolean',
            ],

            /*
            |--------------------------------------------------------------------------
            | Update Transaction Limits
            |--------------------------------------------------------------------------
            */

            'update_transaction_limits' => [
                'bail',
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

            'notify_user' => $this->boolean(
                'notify_user',
                true
            ),

            'update_transaction_limits' => $this->boolean(
                'update_transaction_limits',
                true
            ),

        ]);
    }

    /**
     * Custom Messages
     */
    public function messages(): array
    {
        return [

            'kyc_level.required' => 'KYC level is required.',

            'kyc_level.in' => 'The selected KYC level is invalid.',

            'verification_provider.in' =>
                'The selected verification provider is invalid.',

        ];
    }
}