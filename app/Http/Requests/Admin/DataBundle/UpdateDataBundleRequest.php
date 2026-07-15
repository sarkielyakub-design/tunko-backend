<?php

namespace App\Http\Requests\Admin\DataBundle;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDataBundleRequest extends FormRequest
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
        $bundle = $this->route('data_bundle');

        return [

            /*
            |--------------------------------------------------------------------------
            | Relations
            |--------------------------------------------------------------------------
            */

            'country_id' => [

                'sometimes',

                'required',

                'exists:countries,id',

            ],

            'network_id' => [

                'sometimes',

                'required',

                'exists:networks,id',

            ],

            /*
            |--------------------------------------------------------------------------
            | Provider
            |--------------------------------------------------------------------------
            */

            'provider' => [

                'sometimes',

                'required',

                'string',

                'max:100',

            ],

            'provider_bundle_id' => [

                'sometimes',

                'required',

                'string',

                'max:150',

                Rule::unique(
                    'data_bundles',
                    'provider_bundle_id'
                )->ignore($bundle),

            ],

            /*
            |--------------------------------------------------------------------------
            | Bundle
            |--------------------------------------------------------------------------
            */

            'name' => [

                'sometimes',

                'required',

                'string',

                'max:150',

            ],

            'size' => [

                'sometimes',

                'required',

                'string',

                'max:100',

            ],

            'validity_days' => [

                'sometimes',

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

                'sometimes',

                'required',

                'numeric',

                'min:0',

            ],

            'selling_price' => [

                'sometimes',

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

                'sometimes',

                'boolean',

            ],

        ];
    }

    /**
     * Prepare Data
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('is_active')) {

            $this->merge([

                'is_active' => $this->boolean(
                    'is_active'
                ),

            ]);

        }
    }

    /**
     * Custom Messages
     */
    public function messages(): array
    {
        return [

            'country_id.exists' => 'Selected country does not exist.',

            'network_id.exists' => 'Selected network does not exist.',

            'provider_bundle_id.unique' => 'Provider Bundle ID already exists.',

        ];
    }
}