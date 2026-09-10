<?php

namespace App\Http\Requests\Transfer;

use Illuminate\Foundation\Http\FormRequest;

class VerifyRecipientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Recipient
            |--------------------------------------------------------------------------
            |
            | Can be:
            | - Phone number
            | - Username
            | - Email
            | - Wallet number
            |
            */

            'query' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

        ];
    }

    public function messages(): array
    {
        return [

            'query.required' =>
                'Recipient is required.',

            'query.string' =>
                'Recipient must be a valid text value.',

            'query.min' =>
                'Recipient information is too short.',

            'query.max' =>
                'Recipient information is too long.',

        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([

            'query' => trim(
                (string) $this->input('query')
            ),

        ]);
    }
}