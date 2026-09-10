<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OfficeTransferQuoteRequest extends FormRequest
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
            | Destination Country
            |--------------------------------------------------------------------------
            */

            'destination_office_id' => [
                'nullable',
                'integer',
                'exists:offices,id',
            ],

            'destination_country_id' => [
                'required_without:destination_office_id',
                'integer',
                'exists:countries,id',
            ],

            /*
            |--------------------------------------------------------------------------
            | Destination City
            |--------------------------------------------------------------------------
            */

            'destination_city' => [
                'required_without:destination_office_id',
                'string',
                'max:150',
            ],

            /*
            |--------------------------------------------------------------------------
            | Recipient
            |--------------------------------------------------------------------------
            */

            'recipient_phone' => [
                'required',
                'string',
                'max:30',
            ],

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

            /*
            |--------------------------------------------------------------------------
            | Currency
            |--------------------------------------------------------------------------
            */

            'currency' => [
                'required',
                'string',
                'size:3',
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
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([

            'destination_city' => $this->destination_city !== null
                ? trim((string) $this->destination_city)
                : null,

            'recipient_phone' =>
                trim(
                    (string) $this->recipient_phone
                ),

            'recipient_first_name' =>
                trim(
                    (string) $this->recipient_first_name
                ),

            'recipient_last_name' =>
                trim(
                    (string) $this->recipient_last_name
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
        ]);
    }
}