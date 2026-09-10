<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class VoucherStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reference' => [
                'nullable',
                'string',
                'max:100',
                'unique:vouchers,reference',
            ],

            'type' => [
                'required',
                'in:airtime,data',
            ],

            'country_code' => [
                'required',
                'string',
                'max:5',
            ],

            'network_id' => [
                'nullable',
                'integer',
                'exists:networks,id',
            ],

            'network_name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'product_name' => [
                'nullable',
                'string',
                'max:150',
            ],

            'amount' => [
                'required',
                'numeric',
                'min:0.01',
            ],

            'currency' => [
                'required',
                'string',
                'max:10',
            ],

            'pin' => [
                'required',
                'string',
                'max:500',
            ],

            'provider' => [
                'nullable',
                'string',
                'max:100',
            ],

            'provider_reference' => [
                'nullable',
                'string',
                'max:150',
            ],

            'expires_at' => [
                'nullable',
                'date',
            ],

            'remark' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'meta' => [
                'nullable',
                'array',
            ],
        ];
    }
}
