<?php

namespace App\Http\Requests\Pin;

use Illuminate\Foundation\Http\FormRequest;

class CreatePinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'pin' => [
                'required',
                'digits:4',
                'confirmed',
            ],

        ];
    }

    public function messages(): array
    {
        return [

            'pin.required' => 'Transaction PIN is required.',

            'pin.digits' => 'PIN must be exactly 4 digits.',

            'pin.confirmed' => 'PIN confirmation does not match.',

        ];
    }
}