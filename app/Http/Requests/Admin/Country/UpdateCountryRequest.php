<?php

namespace App\Http\Requests\Admin\Country;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCountryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $country = $this->route('country');

        return [

            'name'=>[
                'sometimes',
                'required',
                'string',
                'max:100',
            ],

            'official_name'=>[
                'nullable',
                'string',
                'max:255',
            ],

            'iso2'=>[
                'sometimes',
                'required',
                'size:2',
                Rule::unique('countries')->ignore($country),
            ],

            'iso3'=>[
                'sometimes',
                'required',
                'size:3',
                Rule::unique('countries')->ignore($country),
            ],

            'continent'=>'nullable|string|max:100',

            'currency'=>'sometimes|required|string|size:3',

            'currency_name'=>'sometimes|required|string|max:100',

            'currency_symbol'=>'nullable|string|max:20',

            'phone_code'=>'sometimes|required|string|max:10',

            'timezone'=>'sometimes|required|string|max:100',

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