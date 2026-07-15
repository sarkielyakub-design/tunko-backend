<?php

namespace App\Http\Requests\Admin\Withdrawal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexWithdrawalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'search' => [
                'nullable',
                'string',
                'max:150',
            ],

            'status' => [
                'nullable',
                Rule::in([
                    'pending',
                    'approved',
                    'processing',
                    'completed',
                    'failed',
                    'cancelled',
                    'rejected',
                    'refunded',
                ]),
            ],

            'provider' => [
                'nullable',
                'string',
                'max:100',
            ],

            'currency' => [
                'nullable',
                'string',
                'max:10',
            ],

            'user_id' => [
                'nullable',
                'exists:users,id',
            ],

            'min_amount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'max_amount' => [
                'nullable',
                'numeric',
                'gte:min_amount',
            ],

            'from_date' => [
                'nullable',
                'date',
            ],

            'to_date' => [
                'nullable',
                'date',
                'after_or_equal:from_date',
            ],

            'sort' => [
                'nullable',
                Rule::in([
                    'created_at',
                    'amount',
                    'status',
                ]),
            ],

            'direction' => [
                'nullable',
                Rule::in([
                    'asc',
                    'desc',
                ]),
            ],

            'per_page' => [
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

            'sort' => $this->sort ?? 'created_at',

            'direction' => $this->direction ?? 'desc',

            'per_page' => $this->per_page ?? 20,

        ]);
    }
}