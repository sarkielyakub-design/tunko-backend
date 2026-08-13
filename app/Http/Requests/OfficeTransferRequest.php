<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OfficeTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Destination
            |--------------------------------------------------------------------------
            */

            'destination_country_id' => [
                'required',
                'integer',
                'exists:countries,id',
            ],

            'destination_city' => [
                'required',
                'string',
                'max:150',
            ],

            /*
            |--------------------------------------------------------------------------
            | Recipient
            |--------------------------------------------------------------------------
            */

            'recipient_first_name' => [
                'required',
                'string',
                'max:100',
            ],

            'recipient_last_name' => [
                'required',
                'string',
                'max:100',
            ],

            'recipient_phone' => [
                'required',
                'string',
                'max:30',
            ],

            /*
            |--------------------------------------------------------------------------
            | Amount
            |--------------------------------------------------------------------------
            */

            'amount' => [
                'required',
                'numeric',
                'min:1',
            ],

            'currency' => [
                'required',
                'string',
                'size:3',
            ],

            /*
            |--------------------------------------------------------------------------
            | Reason
            |--------------------------------------------------------------------------
            */

            'reason' => [
                'nullable',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
                'max:500',
            ],

            /*
            |--------------------------------------------------------------------------
            | Security
            |--------------------------------------------------------------------------
            */

            'pin' => [
                'required',
                'digits:4',
            ],

            /*
            |--------------------------------------------------------------------------
            | Fees
            |--------------------------------------------------------------------------
            */

            'fees_included' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([

            'destination_city' =>
                trim(
                    (string) $this->destination_city
                ),

            'recipient_first_name' =>
                trim(
                    (string) $this->recipient_first_name
                ),

            'recipient_last_name' =>
                trim(
                    (string) $this->recipient_last_name
                ),

            'recipient_phone' =>
                trim(
                    (string) $this->recipient_phone
                ),

            'currency' =>
                strtoupper(
                    trim(
                        (string) $this->currency
                    )
                ),

            'reason' =>
                $this->reason !== null
                    ? trim(
                        (string) $this->reason
                    )
                    : null,

            'description' =>
                $this->description !== null
                    ? trim(
                        (string) $this->description
                    )
                    : null,
        ]);
    }
}