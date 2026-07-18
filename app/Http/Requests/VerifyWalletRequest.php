<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyWalletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'recipient' => [
                'required',
                'string',
                'min:3',
                'max:100',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'recipient.required' => 'Recipient is required.',
        ];
    }
}