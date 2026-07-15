<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],

            'username'   => [
                'required',
                'string',
                'max:50',
                'unique:users,username'
            ],

            'email'      => [
                'required',
                'email',
                'unique:users,email'
            ],

            'phone'      => [
                'required',
                'string',
                'unique:users,phone'
            ],

            'country'    => ['required', 'string'],

            'password'   => [
                'required',
                'confirmed',
                'min:8'
            ],

            'device_name' => [
                'nullable',
                'string'
            ],

            'referral_code' => [
                'nullable',
                'string'
            ],
        ];
    }
}