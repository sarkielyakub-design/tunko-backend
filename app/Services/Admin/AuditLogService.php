<?php

namespace App\Services\Admin;

use App\Models\AuditLog;

class AuditLogService
{
    public function index(array $filters = [])
    {
        return AuditLog::with('admin')

            ->when(
                $filters['search'] ?? null,
                fn($q, $search) =>
                    $q->where('description', 'like', "%{$search}%")
            )

            ->when(
                $filters['module'] ?? null,
                fn($q, $module) =>
                    $q->where('module', $module)
            )

            ->latest()

            ->paginate(
                $filters['per_page'] ?? 20
            );
    }
}