<?php

namespace App\Http\Requests\Transfer;

use Illuminate\Foundation\Http\FormRequest;

class VerifyRecipientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'country_id' => ['required','exists:countries,id'],

            'recipient_type' => ['required','string'],

            'bank_code' => ['required','string'],

            'account_number' => ['required','string'],

            'currency' => ['required','string'],

        ];
    }
}