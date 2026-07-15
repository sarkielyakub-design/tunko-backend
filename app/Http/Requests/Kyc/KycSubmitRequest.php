<?php

namespace App\Http\Requests\Kyc;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class KycSubmitRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
{
    return [
        'document_type' => ['required'],
        'nin' => ['nullable'],
        'passport_number' => ['nullable'],
        'document_front' => ['required','image'],
        'selfie_image' => ['required','image'],
    ];

    }
}
