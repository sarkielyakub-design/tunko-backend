<?php

namespace App\Http\Requests\Admin\Country;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexCountryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'search'=>[
                'nullable',
                'string',
                'max:100',
            ],

            'is_active'=>[
                'nullable',
                'boolean',
            ],

            'sort'=>[
                'nullable',
                Rule::in([
                    'name',
                    'currency',
                    'created_at',
                ]),
            ],

            'direction'=>[
                'nullable',
                Rule::in([
                    'asc',
                    'desc',
                ]),
            ],

            'per_page'=>[
                'nullable',
                'integer',
                'min:10',
                'max:100',
            ],

        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([

            'sort'=>$this->sort ?? 'name',

            'direction'=>$this->direction ?? 'asc',

            'per_page'=>$this->per_page ?? 20,

        ]);
    }
}