<?php

namespace App\Services\Admin;

use App\Models\OfficeStaff;
use Illuminate\Support\Facades\DB;

class OfficeStaffService
{
    public function index(array $filters)
    {
        return OfficeStaff::query()
            ->with('office')
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when(isset($filters['office_id']), fn ($q) => $q->where('office_id', $filters['office_id']))
            ->when(isset($filters['role']), fn ($q) => $q->where('role', $filters['role']))
            ->when(isset($filters['is_active']), fn ($q) => $q->where('is_active', $filters['is_active']))
            ->latest()
            ->paginate($filters['per_page'] ?? 20);
    }

    public function store(array $data): OfficeStaff
    {
        return OfficeStaff::create($data)->load('office');
    }

    public function update(OfficeStaff $staff, array $data): OfficeStaff
    {
        if (array_key_exists('password', $data) && blank($data['password'])) {
            unset($data['password']);
        }

        $staff->update($data);
        return $staff->fresh('office');
    }

    public function destroy(OfficeStaff $staff): void
    {
        DB::transaction(fn () => $staff->delete());
    }

    public function setActive(OfficeStaff $staff, bool $active): OfficeStaff
    {
        $staff->update(['is_active' => $active]);
        return $staff->fresh('office');
    }
}
