<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;

class CreditWalletRequest extends FormRequest
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
     * Custom Messages
     */
    public function messages(): array
    {
        return [

            'amount.required' => 'Credit amount is required.',

            'amount.numeric' => 'Amount must be a valid number.',

            'amount.min' => 'Amount must be greater than zero.',

            'description.required' => 'Please provide a reason for this credit.',

        ];
    }
}