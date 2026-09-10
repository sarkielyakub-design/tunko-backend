<?php

namespace App\Http\Requests\Admin\Network;

use Illuminate\Foundation\Http\FormRequest;

class StoreNetworkRequest extends FormRequest
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
            | Country
            |--------------------------------------------------------------------------
            */

            'country_id' => [

                'required',

                'exists:countries,id',

            ],

            /*
            |--------------------------------------------------------------------------
            | Network
            |--------------------------------------------------------------------------
            */

            'name' => [

                'required',

                'string',

                'max:150',

                'unique:networks,name',

            ],

            'code' => [

                'required',

                'string',

                'max:50',

                'unique:networks,code',

            ],

            'provider' => [

                'nullable',

                'string',

                'max:100',

            ],

            /*
            |--------------------------------------------------------------------------
            | Branding
            |--------------------------------------------------------------------------
            */

            'logo' => [

                'nullable',

                'string',

                'max:255',

            ],

            'color' => [

                'nullable',

                'string',

                'max:30',

            ],

            /*
            |--------------------------------------------------------------------------
            | Services
            |--------------------------------------------------------------------------
            */

            'airtime_enabled' => [

                'boolean',

            ],

            'data_enabled' => [

                'boolean',

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
     * Default Values
     */
    protected function prepareForValidation(): void
    {
        $this->merge([

            'airtime_enabled' => $this->has('airtime_enabled')
                ? $this->boolean('airtime_enabled')
                : true,

            'data_enabled' => $this->has('data_enabled')
                ? $this->boolean('data_enabled')
                : true,

            'is_active' => $this->has('is_active')
                ? $this->boolean('is_active')
                : true,

            'sort_order' => $this->sort_order ?? 0,

        ]);
    }

    /**
     * Messages
     */
    public function messages(): array
    {
        return [

            'country_id.required' => 'Country is required.',

            'country_id.exists' => 'Selected country does not exist.',

            'name.required' => 'Network name is required.',

            'name.unique' => 'Network already exists.',

            'code.required' => 'Network code is required.',

            'code.unique' => 'Network code already exists.',

        ];
    }
}