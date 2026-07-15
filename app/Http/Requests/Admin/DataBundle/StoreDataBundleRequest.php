<?php

namespace App\Http\Requests\Admin\DataBundle;

use Illuminate\Foundation\Http\FormRequest;

class StoreDataBundleRequest extends FormRequest
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
        return [

            /*
            |--------------------------------------------------------------------------
            | Relations
            |--------------------------------------------------------------------------
            */

            'country_id' => [

                'required',

                'exists:countries,id',

            ],

            'network_id' => [

                'required',

                'exists:networks,id',

            ],

            /*
            |--------------------------------------------------------------------------
            | Provider
            |--------------------------------------------------------------------------
            */

            'provider' => [

                'required',

                'string',

                'max:100',

            ],

            'provider_bundle_id' => [

                'required',

                'string',

                'max:150',

                'unique:data_bundles,provider_bundle_id',

            ],

            /*
            |--------------------------------------------------------------------------
            | Bundle
            |--------------------------------------------------------------------------
            */

            'name' => [

                'required',

                'string',

                'max:150',

            ],

            'size' => [

                'required',

                'string',

                'max:100',

            ],

            'validity_days' => [

                'required',

                'integer',

                'min:1',

            ],

            /*
            |--------------------------------------------------------------------------
            | Pricing
            |--------------------------------------------------------------------------
            */

            'provider_price' => [

                'required',

                'numeric',

                'min:0',

            ],

            'selling_price' => [

                'required',

                'numeric',

                'min:0',

            ],

            'commission' => [

                'nullable',

                'numeric',

                'min:0',

            ],

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            'sort_order' => [

                'nullable',

                'integer',

                'min:0',

            ],

            'is_active' => [

                'boolean',

            ],

        ];
    }

    /**
     * Prepare Data
     */
    protected function prepareForValidation(): void
    {
        $this->merge([

            'commission' => $this->commission ?? 0,

            'sort_order' => $this->sort_order ?? 0,

            'is_active' => $this->has('is_active')

                ? $this->boolean('is_active')

                : true,

        ]);
    }

    /**
     * Messages
     */
    public function messages(): array
    {
        return [

            'country_id.required' => 'Country is required.',

            'network_id.required' => 'Network is required.',

            'provider.required' => 'Provider is required.',

            'provider_bundle_id.required' => 'Provider Bundle ID is required.',

            'provider_bundle_id.unique' => 'Provider Bundle ID already exists.',

            'name.required' => 'Bundle name is required.',

            'size.required' => 'Bundle size is required.',

            'validity_days.required' => 'Validity is required.',

            'provider_price.required' => 'Provider price is required.',

            'selling_price.required' => 'Selling price is required.',

        ];
    }
}