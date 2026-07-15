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
            */

            'recipient_id' => [
                'required',
                'integer',
                'exists:users,id',
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
            | Remark
            |--------------------------------------------------------------------------
            */

            'remark' => [
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

            'recipient_id.required' =>
                'Recipient is required.',

            'recipient_id.exists' =>
                'Recipient not found.',

            'amount.required' =>
                'Transfer amount is required.',

            'amount.numeric' =>
                'Amount must be numeric.',

            'amount.min' =>
                'Minimum transfer amount is ₦1.',

            'amount.max' =>
                'Maximum transfer amount exceeded.',

            'pin.required' =>
                'Transaction PIN is required.',

            'pin.digits' =>
                'Transaction PIN must be exactly 4 digits.',

            'remark.max' =>
                'Remark cannot exceed 255 characters.',
        ];
    }

    /**
     * Prepare data before validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([

            'recipient_id' => (int) $this->recipient_id,

            'amount' => (float) $this->amount,

            'remark' => trim($this->remark ?? ''),

        ]);
    }
}