<?php

namespace App\Http\Requests\Admin\Wallet;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FreezeWalletRequest extends FormRequest
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
            | Freeze Reason
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
            | Freeze Type
            |--------------------------------------------------------------------------
            */

            'freeze_type' => [

                'required',

                Rule::in([

                    'temporary',

                    'permanent',

                    'compliance',

                    'fraud',

                    'security',

                    'legal',

                ]),

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
            | Expiry Date
            |--------------------------------------------------------------------------
            */

            'expires_at' => [

                'nullable',

                'date',

                'after:now',

            ],

            /*
            |--------------------------------------------------------------------------
            | Notification
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

        ]);
    }

    /**
     * Messages
     */
    public function messages(): array
    {
        return [

            'reason.required' => 'Freeze reason is required.',

            'freeze_type.required' => 'Freeze type is required.',

            'freeze_type.in' => 'Invalid freeze type selected.',

            'expires_at.after' => 'Expiry date must be in the future.',

        ];
    }
}