<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Services\Office\OfficeDashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private OfficeDashboardService $service) {}

    public function index(Request $request)
    {
        return response()->json(['success' => true, 'data' => $this->service->dashboard($request->user())]);
    }
}
