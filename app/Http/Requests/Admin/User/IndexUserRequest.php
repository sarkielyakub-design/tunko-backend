<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexUserRequest extends FormRequest
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
            | Pagination
            |--------------------------------------------------------------------------
            */

            'page' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'per_page' => [
                'nullable',
                'integer',
                Rule::in([10, 20, 50, 100]),
            ],

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

            'country' => [
                'nullable',
                'string',
                'max:100',
            ],

            'status' => [
                'nullable',
                Rule::in([
                    'active',
                    'inactive',
                ]),
            ],

            'verified' => [
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
                    'first_name',
                    'last_name',
                    'username',
                    'email',
                    'phone',
                    'country',
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

        ];
    }

    /**
     * Default Values
     */
    protected function prepareForValidation(): void
    {
        $this->merge([

            'per_page' => $this->per_page ?? 20,

            'sort' => $this->sort ?? 'created_at',

            'direction' => $this->direction ?? 'desc',

        ]);
    }
}