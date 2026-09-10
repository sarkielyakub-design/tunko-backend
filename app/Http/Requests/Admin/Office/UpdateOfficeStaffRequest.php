<?php

namespace App\Http\Requests\Admin\Office;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOfficeStaffRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $staff = $this->route('officeStaff');
        $staffId = is_object($staff) ? $staff->id : $staff;

        return [
            'office_id' => ['sometimes', 'required', 'integer', 'exists:offices,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('office_staff', 'email')->ignore($staffId)],
            'phone' => ['nullable', 'string', 'max:30', Rule::unique('office_staff', 'phone')->ignore($staffId)],
            'password' => ['nullable', 'string', 'min:8', 'max:72'],
            'role' => ['sometimes', Rule::in(['manager', 'agent'])],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('is_active')) {
            $this->merge(['is_active' => $this->boolean('is_active')]);
        }
    }
}
