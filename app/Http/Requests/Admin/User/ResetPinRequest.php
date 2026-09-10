<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;

class ResetPinRequest extends FormRequest
{
    /**
     * Authorize
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Rules
     */
    public function rules(): array
    {
        return [

            'pin' => [

                'required',

                'digits:4',

            ],

        ];
    }

    /**
     * Messages
     */
    public function messages(): array
    {
        return [

            'pin.required' =>
                'Transaction PIN is required.',

            'pin.digits' =>
                'PIN must be exactly 4 digits.',

        ];
    }
}