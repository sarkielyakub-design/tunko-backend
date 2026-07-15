<?php

namespace App\Http\Requests\Airtime;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
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

        "country_id" => "required|exists:countries,id",

        "network_id" => "required",

        "phone" => "required|string",

        "amount" => "required|numeric|min:100",

        "pin" => "required|digits:4",

    ];
}
}
