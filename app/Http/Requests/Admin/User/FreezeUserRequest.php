<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FreezeUserRequest extends FormRequest
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

            'status' => [
                'required',
                Rule::in([
                    'freeze',
                    'unfreeze',
                ]),
            ],

            'reason' => [
                'required',
                'string',
                'min:5',
                'max:255',
            ],

        ];
    }

    /**
     * Messages
     */
    public function messages(): array
    {
        return [

            'reason.required' => 'Please provide a reason.',

            'status.required' => 'Status is required.',

        ];
    }
}