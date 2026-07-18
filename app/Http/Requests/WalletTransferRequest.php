<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WalletTransferRequest extends FormRequest
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
            ],

            'amount' => [
                'required',
                'numeric',
                'min:1',
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
}