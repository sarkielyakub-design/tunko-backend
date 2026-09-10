<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WalletTransferQuoteRequest extends FormRequest
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
        ];
    }
}