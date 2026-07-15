<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
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

            'password' => [

                'required',

                'confirmed',

                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),

            ],

        ];
    }

    /**
     * Messages
     */
    public function messages(): array
    {
        return [

            'password.confirmed' =>
                'Password confirmation does not match.',

        ];
    }
}