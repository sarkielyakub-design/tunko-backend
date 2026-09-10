<?php

namespace App\Http\Requests\Admin\Country;

use Illuminate\Foundation\Http\FormRequest;

class StoreCountryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'name'=>'required|string|max:100',

            'official_name'=>'nullable|string|max:255',

            'iso2'=>'required|string|size:2|unique:countries',

            'iso3'=>'required|string|size:3|unique:countries',

            'continent'=>'nullable|string|max:100',

            'currency'=>'required|string|size:3',

            'currency_name'=>'required|string|max:100',

            'currency_symbol'=>'nullable|string|max:20',

            'phone_code'=>'required|string|max:10',

            'timezone'=>'required|string|max:100',

            'flag'=>'nullable|string',

            'exchange_rate'=>'nullable|numeric|min:0',

            'wallet_enabled'=>'boolean',

            'transfer_enabled'=>'boolean',

            'airtime_enabled'=>'boolean',

            'data_enabled'=>'boolean',

            'kyc_required'=>'boolean',

            'minimum_transfer'=>'numeric|min:0',

            'maximum_transfer'=>'numeric|min:0',

            'minimum_wallet_funding'=>'numeric|min:0',

            'maximum_wallet_funding'=>'numeric|min:0',

            'sort_order'=>'integer|min:0',

            'is_active'=>'boolean',

        ];
    }
}