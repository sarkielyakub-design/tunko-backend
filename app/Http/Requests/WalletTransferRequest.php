<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WalletTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [

            'recipient' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'amount' => [
                'required',
                'numeric',
                'min:1',
                'max:10000000',
            ],

            'pin' => [
                'required',
                'digits:4',
            ],

            'description' => [
                'nullable',
                'string',
                'max:255',
            ],

        ];
    }

    public function messages(): array
    {
        return [

            'recipient.required' =>
                'Recipient is required.',

            'recipient.min' =>
                'Recipient information is too short.',

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

    protected function prepareForValidation(): void
    {
        $this->merge([

            'recipient' =>
                trim(
                    (string)
                    $this->input('recipient')
                ),

            'amount' =>
                (float)
                $this->input('amount'),

            'description' =>
                trim(
                    (string)
                    $this->input(
                        'description',
                        ''
                    )
                ) ?: null,

        ]);
    }
}