<?php

namespace App\Http\Requests\Data;

use Illuminate\Foundation\Http\FormRequest;

class DataPurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'country_code' => [
                'required',
                'string',
                'size:2',
            ],

            'network_id' => [
                'required',
                'integer',
            ],

            'bundle_id' => [
                'required',
                'integer',
            ],

            'phone' => [
                'required',
                'string',
                'min:8',
                'max:20',
            ],

            'pin' => [
                'required',
                'digits:4',
            ],

            'network_name' => [
                'nullable',
                'string',
            ],

            'bundle_name' => [
                'nullable',
                'string',
            ],

        ];
    }

    public function messages(): array
    {
        return [

            'country_code.required' =>
                'Country is required.',

            'country_code.size' =>
                'Invalid country code.',

            'network_id.required' =>
                'Network is required.',

            'bundle_id.required' =>
                'Bundle is required.',

            'phone.required' =>
                'Phone number is required.',

            'pin.required' =>
                'Transaction PIN is required.',

            'pin.digits' =>
                'PIN must be exactly 4 digits.',

        ];
    }
}