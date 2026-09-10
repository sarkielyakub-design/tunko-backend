<?php

namespace App\Http\Requests\Wallet;

use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest
{
    /**
     * Authorize request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Validation Rules.
     */
    public function rules(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Recipient
            |--------------------------------------------------------------------------
            |
            | The Flutter application sends the recipient as:
            |
            | - Wallet number
            | - Phone number
            | - Username
            | - Email
            |
            */

            'recipient' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            /*
            |--------------------------------------------------------------------------
            | Amount
            |--------------------------------------------------------------------------
            */

            'amount' => [
                'required',
                'numeric',
                'min:1',
                'max:10000000',
            ],

            /*
            |--------------------------------------------------------------------------
            | Transaction PIN
            |--------------------------------------------------------------------------
            */

            'pin' => [
                'required',
                'digits:4',
            ],

            /*
            |--------------------------------------------------------------------------
            | Description
            |--------------------------------------------------------------------------
            */

            'description' => [
                'nullable',
                'string',
                'max:255',
            ],

        ];
    }

    /**
     * Custom Validation Messages.
     */
    public function messages(): array
    {
        return [

            'recipient.required' =>
                'Recipient is required.',

            'recipient.string' =>
                'Recipient must be a valid value.',

            'recipient.min' =>
                'Recipient information is too short.',

            'recipient.max' =>
                'Recipient information is too long.',

            'amount.required' =>
                'Transfer amount is required.',

            'amount.numeric' =>
                'Amount must be numeric.',

            'amount.min' =>
                'Minimum transfer amount is 1.',

            'amount.max' =>
                'Maximum transfer amount exceeded.',

            'pin.required' =>
                'Transaction PIN is required.',

            'pin.digits' =>
                'Transaction PIN must be exactly 4 digits.',

            'description.max' =>
                'Description cannot exceed 255 characters.',

        ];
    }

    /**
     * Prepare data before validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([

            'recipient' => trim(
                (string) $this->input('recipient')
            ),

            'amount' => (float) $this->input('amount'),

            'description' => trim(
                (string) $this->input('description', '')
            ) ?: null,

        ]);
    }
}