<?php

namespace App\Http\Requests\Admin\Network;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNetworkRequest extends FormRequest
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
        $network = $this->route('network');

        return [

            /*
            |--------------------------------------------------------------------------
            | Country
            |--------------------------------------------------------------------------
            */

            'country_id' => [

                'sometimes',

                'required',

                'exists:countries,id',

            ],

            /*
            |--------------------------------------------------------------------------
            | Network
            |--------------------------------------------------------------------------
            */

            'name' => [

                'sometimes',

                'required',

                'string',

                'max:150',

                Rule::unique('networks', 'name')
                    ->ignore($network),

            ],

            'code' => [

                'sometimes',

                'required',

                'string',

                'max:50',

                Rule::unique('networks', 'code')
                    ->ignore($network),

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

                'sometimes',

                'boolean',

            ],

            'data_enabled' => [

                'sometimes',

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
        if ($this->has('airtime_enabled')) {

            $this->merge([

                'airtime_enabled' => $this->boolean(
                    'airtime_enabled'
                ),

            ]);

        }

        if ($this->has('data_enabled')) {

            $this->merge([

                'data_enabled' => $this->boolean(
                    'data_enabled'
                ),

            ]);

        }

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

            'name.unique' => 'Network name already exists.',

            'code.unique' => 'Network code already exists.',

        ];
    }
}