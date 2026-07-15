<?php

namespace App\Http\Requests\Admin\Office;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOfficeRequest extends FormRequest
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
        $office = $this->route('office');

        return [

            /*
            |--------------------------------------------------------------------------
            | Basic Information
            |--------------------------------------------------------------------------
            */

            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('offices', 'name')
                    ->ignore($office),
            ],

            'country' => [
                'sometimes',
                'required',
                'string',
                'max:100',
            ],

            'state' => [
                'nullable',
                'string',
                'max:100',
            ],

            'city' => [
                'sometimes',
                'required',
                'string',
                'max:100',
            ],

            /*
            |--------------------------------------------------------------------------
            | Contact
            |--------------------------------------------------------------------------
            */

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'whatsapp' => [
                'nullable',
                'string',
                'max:30',
            ],

            /*
            |--------------------------------------------------------------------------
            | Address
            |--------------------------------------------------------------------------
            */

            'address' => [
                'sometimes',
                'required',
                'string',
                'max:1000',
            ],

            'latitude' => [
                'nullable',
                'numeric',
                'between:-90,90',
            ],

            'longitude' => [
                'nullable',
                'numeric',
                'between:-180,180',
            ],

            /*
            |--------------------------------------------------------------------------
            | Business
            |--------------------------------------------------------------------------
            */

            'timezone' => [
                'sometimes',
                'required',
                'string',
                'max:100',
            ],

            'currency' => [
                'sometimes',
                'required',
                'string',
                'size:3',
            ],

            'is_head_office' => [
                'sometimes',
                'boolean',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

            /*
            |--------------------------------------------------------------------------
            | SEO
            |--------------------------------------------------------------------------
            */

            'meta_title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'meta_description' => [
                'nullable',
                'string',
                'max:1000',
            ],

        ];
    }

    /**
     * Prepare Data
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('currency')) {

            $this->merge([
                'currency' => strtoupper($this->currency),
            ]);

        }

        if ($this->has('is_active')) {

            $this->merge([
                'is_active' => $this->boolean('is_active'),
            ]);

        }

        if ($this->has('is_head_office')) {

            $this->merge([
                'is_head_office' => $this->boolean('is_head_office'),
            ]);

        }
    }

    /**
     * Custom Messages
     */
    public function messages(): array
    {
        return [

            'name.unique' => 'Office name already exists.',

            'currency.size' => 'Currency must be a valid ISO code.',

            'latitude.between' => 'Latitude must be between -90 and 90.',

            'longitude.between' => 'Longitude must be between -180 and 180.',

        ];
    }
}