<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Authorize
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation Rules
     */
    public function rules(): array
    {
        $user = $this->route('user');

        return [

            /*
            |--------------------------------------------------------------------------
            | Personal Information
            |--------------------------------------------------------------------------
            */

            'first_name' => [
                'sometimes',
                'required',
                'string',
                'max:100',
            ],

            'last_name' => [
                'sometimes',
                'required',
                'string',
                'max:100',
            ],

            'username' => [
                'sometimes',
                'required',
                'string',
                'min:3',
                'max:50',
                Rule::unique('users', 'username')
                    ->ignore($user),
            ],

            /*
            |--------------------------------------------------------------------------
            | Contact
            |--------------------------------------------------------------------------
            */

            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')
                    ->ignore($user),
            ],

            'phone' => [
                'sometimes',
                'required',
                'string',
                'max:30',
                Rule::unique('users', 'phone')
                    ->ignore($user),
            ],

            'country' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
            ],

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            'is_verified' => [
                'sometimes',
                'boolean',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],

        ];
    }

    /**
     * Default Values
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('is_verified')) {
            $this->merge([
                'is_verified' => $this->boolean('is_verified'),
            ]);
        }

        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => $this->boolean('is_active'),
            ]);
        }
    }

    /**
     * Messages
     */
    public function messages(): array
    {
        return [

            'username.unique' => 'Username already exists.',

            'email.unique' => 'Email already exists.',

            'phone.unique' => 'Phone number already exists.',

        ];
    }
}