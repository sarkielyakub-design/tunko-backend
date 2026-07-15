<?php

namespace App\Http\Requests\Admin\DataBundle;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexDataBundleRequest extends FormRequest
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
            | Search
            |--------------------------------------------------------------------------
            */

            'search' => [
                'nullable',
                'string',
                'max:100',
            ],

            /*
            |--------------------------------------------------------------------------
            | Filters
            |--------------------------------------------------------------------------
            */

            'country_id' => [
                'nullable',
                'exists:countries,id',
            ],

            'network_id' => [
                'nullable',
                'exists:networks,id',
            ],

            'provider' => [
                'nullable',
                'string',
                'max:100',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            /*
            |--------------------------------------------------------------------------
            | Sorting
            |--------------------------------------------------------------------------
            */

            'sort' => [
                'nullable',
                Rule::in([
                    'id',
                    'name',
                    'selling_price',
                    'provider_price',
                    'sort_order',
                    'created_at',
                ]),
            ],

            'direction' => [
                'nullable',
                Rule::in([
                    'asc',
                    'desc',
                ]),
            ],

            /*
            |--------------------------------------------------------------------------
            | Pagination
            |--------------------------------------------------------------------------
            */

            'per_page' => [
                'nullable',
                'integer',
                'min:10',
                'max:100',
            ],

        ];
    }

    /**
     * Default Values
     */
    protected function prepareForValidation(): void
    {
        $this->merge([

            'sort' => $this->sort ?? 'sort_order',

            'direction' => $this->direction ?? 'asc',

            'per_page' => $this->per_page ?? 20,

        ]);
    }
}