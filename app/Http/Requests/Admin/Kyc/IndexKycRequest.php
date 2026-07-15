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

                'nullable',

                Rule::in([

                    1,

                    2,

                    3,

                    4,

                ]),

            ],

            'country' => [

                'nullable',

                'string',

                'max:100',

            ],

            'document_type' => [

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

                'nullable',

                'exists:users,id',

            ],

            /*
            |--------------------------------------------------------------------------
            | Date
            |--------------------------------------------------------------------------
            */

            'from_date' => [

                'nullable',

                'date',

            ],

            'to_date' => [

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

                'nullable',

                Rule::in([

                    'created_at',

                    'status',

                    'level',

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
     * Prepare Validation
     */
    protected function prepareForValidation(): void
    {
        $this->merge([

            'sort' => $this->sort ?? 'created_at',

            'direction' => $this->direction ?? 'desc',

            'per_page' => $this->per_page ?? 20,

        ]);
    }
}