<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\Admin\AuditLogResource;
use App\Services\Admin\AuditLogService;
use Illuminate\Http\Request;

class AuditLogController extends AdminController
{
    public function __construct(
        private AuditLogService $service
    ) {
    }

    public function index(Request $request)
    {
        $logs = $this->service->index(
            $request->all()
        );

        return $this->success([
            "logs" => AuditLogResource::collection($logs),

            "pagination" => [

                "current_page" => $logs->currentPage(),

                "last_page" => $logs->lastPage(),

                "total" => $logs->total(),

            ],

        ]);
    }
}