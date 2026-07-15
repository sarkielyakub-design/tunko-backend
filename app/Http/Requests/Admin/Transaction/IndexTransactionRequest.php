<?php

namespace App\Http\Requests\Admin\Transaction;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexTransactionRequest extends FormRequest
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
                    'processing',
                    'success',
                    'failed',
                    'cancelled',
                    'refunded',
                    'reversed',
                ]),
            ],

            'type' => [
                'nullable',
                Rule::in([
                    'deposit',
                    'withdraw',
                    'transfer',
                    'wallet_credit',
                    'wallet_debit',
                    'airtime',
                    'data',
                    'bill',
                    'refund',
                    'reversal',
                ]),
            ],

            'currency' => [
                'nullable',
                'string',
                'max:10',
            ],

            'payment_gateway' => [
                'nullable',
                'string',
                'max:100',
            ],

            'user_id' => [
                'nullable',
                'exists:users,id',
            ],

            /*
            |--------------------------------------------------------------------------
            | Amount
            |--------------------------------------------------------------------------
            */

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
                    'amount',
                    'status',
                    'type',
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

            'sort' => $this->sort ?? 'created_at',

            'direction' => $this->direction ?? 'desc',

            'per_page' => $this->per_page ?? 20,

        ]);
    }
}