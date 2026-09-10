<?php

namespace App\Http\Requests\Admin\Kyc;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexKycRequest extends FormRequest
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
                'bail',
                'nullable',
                'string',
                'max:150',
            ],

            /*
            |--------------------------------------------------------------------------
            | Filters
            |--------------------------------------------------------------------------
            */

            'status' => [
                'bail',
                'nullable',
                Rule::in([
                    'pending',
                    'under_review',
                    'approved',
                    'rejected',
                    'expired',
                ]),
            ],

            'level' => [
                'bail',
                'nullable',
                Rule::in([
                    1,
                    2,
                    3,
                    4,
                ]),
            ],

            'country' => [
                'bail',
                'nullable',
                'string',
                'max:100',
            ],

            'document_type' => [
                'bail',
                'nullable',
                Rule::in([
                    'passport',
                    'national_id',
                    'drivers_license',
                    'residence_permit',
                    'voter_card',
                ]),
            ],

            'user_id' => [
                'bail',
                'nullable',
                'integer',
                'exists:users,id',
            ],

            /*
            |--------------------------------------------------------------------------
            | Date Filters
            |--------------------------------------------------------------------------
            */

            'from_date' => [
                'bail',
                'nullable',
                'date',
            ],

            'to_date' => [
                'bail',
                'nullable',
                'date',
                'after_or_equal:from_date',
            ],

            /*
            |--------------------------------------------------------------------------
            | Sorting
            |--------------------------------------------------------------------------
            */

            'sort' => [
                'bail',
                'nullable',
                Rule::in([
                    'created_at',
                    'status',
                    'level',
                ]),
            ],

            'direction' => [
                'bail',
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

            'page' => [
                'bail',
                'nullable',
                'integer',
                'min:1',
            ],

            'per_page' => [
                'bail',
                'nullable',
                'integer',
                'min:10',
                'max:100',
            ],
        ];
    }

    /**
     * Prepare Validation
     */
    protected function prepareForValidation(): void
    {
        $this->merge([

            'sort' => $this->input('sort', 'created_at'),

            'direction' => $this->input('direction', 'desc'),

            'per_page' => $this->input('per_page', 20),

            'page' => $this->input('page', 1),

        ]);
    }
}