<?php

namespace App\Services\Office;

use App\Models\OfficeStaff;
use App\Models\OfficeTransfer;
use Illuminate\Support\Facades\DB;

class OfficeDashboardService
{
    public function dashboard(OfficeStaff $staff): array
    {
        $base = OfficeTransfer::query()->where('destination_office_id', $staff->office_id);
        return [
            'office' => $staff->office->only(['id','name','country','state','city','address','phone','currency','timezone','is_active']),
            'counts' => [
                'total' => (clone $base)->count(),
                'pending' => (clone $base)->where('status', 'pending')->count(),
                'processing' => (clone $base)->where('status', 'processing')->count(),
                'completed' => (clone $base)->where('status', 'completed')->count(),
                'cancelled' => (clone $base)->where('status', 'cancelled')->count(),
            ],
            'today' => [
                'total' => (clone $base)->whereDate('created_at', today())->count(),
                'pending' => (clone $base)->whereDate('created_at', today())->where('status', 'pending')->count(),
                'processing' => (clone $base)->whereDate('created_at', today())->where('status', 'processing')->count(),
                'completed' => (clone $base)->whereDate('created_at', today())->where('status', 'completed')->count(),
            ],
        ];
    }
}
