<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyWalletRequest extends FormRequest
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

        ];
    }

    public function messages(): array
    {
        return [

            'recipient.required' =>
                'Recipient is required.',

            'recipient.min' =>
                'Recipient information is too short.',

            'recipient.max' =>
                'Recipient information is too long.',

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

        ]);
    }
}