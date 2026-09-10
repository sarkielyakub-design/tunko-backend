<?php

namespace App\Http\Requests\Admin\Office;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOfficeTransferStatusRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:pending,processing,completed,cancelled,failed'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
