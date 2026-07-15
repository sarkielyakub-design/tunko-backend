<?php

namespace App\Http\Requests\Admin\Network;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexNetworkRequest extends FormRequest
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
                'integer',
                'exists:countries,id',
            ],

            'airtime_enabled' => [
                'nullable',
                'boolean',
            ],

            'data_enabled' => [
                'nullable',
                'boolean',
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
                    'provider',
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