<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;

class DebitWalletRequest extends FormRequest
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
            | Amount
            |--------------------------------------------------------------------------
            */

            'amount' => [
                'required',
                'numeric',
                'min:1',
                'max:1000000000',
            ],

            /*
            |--------------------------------------------------------------------------
            | Description
            |--------------------------------------------------------------------------
            */

            'description' => [
                'required',
                'string',
                'min:5',
                'max:255',
            ],

        ];
    }

    /**
     * Messages
     */
    public function messages(): array
    {
        return [

            'amount.required' => 'Debit amount is required.',

            'description.required' => 'Please provide a reason for this debit.',

        ];
    }
}