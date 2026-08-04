<?php

namespace App\Http\Requests\Airtime;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
{
    /**
     * Authorize the request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules.
     */
    public function rules(): array
    {
        return [

            'country_code' => [
                'required',
                'string',
                'size:2',
            ],

            'operator_id' => [
                'required',
                'integer',
            ],

            'country' => [
                'required',
                'string',
            ],

            'network' => [
                'required',
                'string',
            ],

            'phone' => [
                'required',
                'string',
                'min:8',
                'max:20',
            ],

            'amount' => [
                'required',
                'numeric',
                'min:1',
            ],

            'pin' => [
                'required',
                'digits:4',
            ],

        ];
    }

    public function messages(): array
    {
        return [

            'country_code.required' => 'Country is required.',

            'operator_id.required' => 'Operator is required.',

            'country.required' => 'Country is required.',

            'network.required' => 'Network is required.',

            'phone.required' => 'Phone number is required.',

            'amount.required' => 'Amount is required.',

            'pin.required' => 'Transaction PIN is required.',

            'pin.digits' => 'PIN must be exactly 4 digits.',

        ];
    }
}