<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\DashboardService;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $service
    ) {
    }

    public function index()
    {
        return response()->json([

            "success" => true,

            "data" => $this->service->dashboard(),

        ]);
    }
}